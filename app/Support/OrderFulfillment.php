<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Trial;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * What happens when money moves. Everything runs in one transaction with the order locked, so a
 * double click (or a future gateway webhook calling the same methods) can't fulfil an order twice.
 */
class OrderFulfillment
{
    /**
     * Records a successful payment: the order becomes paid and its line is created or extended.
     *
     * @param  array{provider: string, provider_payment_id?: ?string, payload?: ?array}  $payment
     * @param  array{device_type?: string, device_info?: ?string, packs?: list<int>, trial_id?: ?int}  $line  Needed for a new order only.
     */
    public function pay(Order $order, array $payment, array $line = []): Payment
    {
        return DB::transaction(function () use ($order, $payment, $line) {
            $order = $this->lockPayable($order);
            $record = $this->addPayment($order, $payment, 'succeeded');

            $subscription = $order->type === 'renewal' ? $this->renew($order) : $this->createLine($order, $line);

            $order->update(['status' => 'paid', 'paid_at' => now(), 'subscription_id' => $subscription->id]);

            if ($trialId = $line['trial_id'] ?? null) {
                Trial::whereKey($trialId)->where('customer_id', $order->customer_id)->whereNotIn('status', ['converted', 'rejected'])
                    ->first()?->update(['status' => 'converted', 'converted_subscription_id' => $subscription->id]);
            }

            return $record;
        });
    }

    /**
     * Records a failed attempt. The order can still be paid afterwards.
     *
     * @param  array{provider: string, provider_payment_id?: ?string, failure_reason?: ?string, payload?: ?array}  $payment
     */
    public function fail(Order $order, array $payment): Payment
    {
        return DB::transaction(function () use ($order, $payment) {
            $order = $this->lockPayable($order);
            $record = $this->addPayment($order, $payment, 'failed');
            $order->update(['status' => 'failed']);

            return $record;
        });
    }

    /**
     * Marks a paid order and its payment as refunded. The money is returned at the gateway;
     * cancelling the line is optional because a refund can be a goodwill gesture.
     */
    public function refund(Order $order, bool $cancelLine): void
    {
        DB::transaction(function () use ($order, $cancelLine) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'paid') {
                throw ValidationException::withMessages(['order' => "Only paid orders can be refunded; this one is {$order->status}."]);
            }

            $order->update(['status' => 'refunded']);
            $order->payments()->where('status', 'succeeded')->get()->each->update(['status' => 'refunded']);

            if ($cancelLine) {
                $order->subscription?->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            }
        });
    }

    /**
     * Same result as SQL's "+ INTERVAL n MONTH": Jan 31 plus a month is Feb 28, not Mar 3.
     */
    public static function addMonths(CarbonInterface $date, int $months): CarbonInterface
    {
        return $date->copy()->addMonthsNoOverflow($months);
    }

    private function lockPayable(Order $order): Order
    {
        $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

        if (! $order->isPayable()) {
            throw ValidationException::withMessages(['order' => "This order is already {$order->status}."]);
        }

        return $order;
    }

    /**
     * The amount is always the order's total: a short payment doesn't buy a line.
     *
     * @param  array<string, mixed>  $payment
     */
    private function addPayment(Order $order, array $payment, string $status): Payment
    {
        return $order->payments()->create([
            'provider' => $payment['provider'],
            'provider_payment_id' => $payment['provider_payment_id'] ?? null,
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => $status,
            'failure_reason' => $payment['failure_reason'] ?? null,
            'payload' => $payment['payload'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function createLine(Order $order, array $line): Subscription
    {
        $subscription = Subscription::create([
            'customer_id' => $order->customer_id,
            'plan_id' => $order->plan_id,
            'device_type' => $line['device_type'],
            'device_info' => $line['device_info'] ?? null,
            'status' => 'active',
            'starts_at' => now(),
            'expires_at' => self::addMonths(now(), $order->duration_months),
        ]);
        $subscription->packs()->sync($line['packs'] ?? []);

        return $subscription;
    }

    /**
     * Time left is kept: the months are added to the current end date, or to today if it has passed.
     */
    private function renew(Order $order): Subscription
    {
        $subscription = Subscription::whereKey($order->subscription_id)->lockForUpdate()->firstOrFail();
        $from = $subscription->expires_at?->isFuture() ? $subscription->expires_at : now();

        $subscription->update([
            'plan_id' => $order->plan_id,
            'status' => 'active',
            'starts_at' => $subscription->starts_at ?? now(),
            'expires_at' => self::addMonths($from, $order->duration_months),
            'cancelled_at' => null,
        ]);

        return $subscription;
    }
}
