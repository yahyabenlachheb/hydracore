<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Pack;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Trial;
use App\Support\OrderFulfillment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('orders.view');

        $filters = [
            'q' => trim((string) $request->query('q')),
            'status' => array_key_exists((string) $request->query('status'), Order::STATUSES) ? $request->query('status') : null,
            'type' => in_array($request->query('type'), ['new', 'renewal'], true) ? $request->query('type') : null,
        ];

        $orders = Order::visibleTo($this->brandId())
            ->with('customer:id,full_name,email')
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('reference_number', 'like', "%{$filters['q']}%")
                ->orWhere('coupon_code', 'like', "%{$filters['q']}%")
                ->orWhereHas('customer', fn ($query) => $query->matching($filters['q']))))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'], fn ($query, $type) => $query->where('type', $type))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('orders.list', compact('orders', 'filters'));
    }

    /**
     * ?subscription=ID starts a renewal; ?customer=ID preselects the buyer; ?trial=ID converts a trial.
     */
    public function create(Request $request): View
    {
        Gate::authorize('orders.create');
        $brandId = $this->brandId();

        $subscription = $request->integer('subscription')
            ? Subscription::visibleTo($brandId)->with('customer')->findOrFail($request->integer('subscription'))
            : null;
        $trial = $request->integer('trial')
            ? Trial::visibleTo($brandId)->with('customer')->findOrFail($request->integer('trial'))
            : null;
        $customer = $subscription?->customer ?? $trial?->customer
            ?? ($request->integer('customer') ? Customer::visibleTo($brandId)->find($request->integer('customer')) : null);

        return view('orders.create', [
            'plans' => Plan::visibleTo($brandId)->where('is_active', true)->orderBy('sort_order')->orderBy('duration_months')->get(),
            'subscription' => $subscription,
            'trial' => $trial,
            'customer' => $customer,
        ]);
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $plan = Plan::visibleTo($this->brandId())->findOrFail($request->validated('plan_id'));
        $discount = (string) ($request->validated('discount') ?? '0');

        $order = Order::create([
            'reference_number' => Order::newReference(),
            'customer_id' => $request->validated('customer_id'),
            'plan_id' => $plan->id,
            'subscription_id' => $request->validated('subscription_id'),
            'brand_id' => $this->brandId(),
            'type' => $request->validated('subscription_id') ? 'renewal' : 'new',
            'plan_name' => $plan->name,
            'duration_months' => $plan->duration_months,
            'subtotal' => $plan->price,
            'discount' => $discount,
            'total' => number_format((float) $plan->price - (float) $discount, 2, '.', ''),
            'currency' => $plan->currency,
            'coupon_code' => $request->validated('coupon_code'),
            'description' => $request->validated('description'),
        ]);

        return redirect()->route('orders.show', array_filter(['order' => $order, 'trial' => $request->integer('trial') ?: null]))
            ->with('success', "Order {$order->reference_number} created. Record the payment when it arrives.");
    }

    public function show(Request $request, Order $order): View
    {
        Gate::authorize('orders.view');
        $this->ensureVisible($order);

        // Trials this order could convert; the one the order was started from comes preselected.
        $openTrials = $order->isPayable() && $order->type === 'new'
            ? Trial::visibleTo($this->brandId())->where('customer_id', $order->customer_id)->whereIn('status', ['pending', 'active', 'expired'])->with('packs:id')->latest('id')->get()
            : collect();

        return view('orders.show', [
            'order' => $order->load(['customer', 'subscription', 'payments' => fn ($query) => $query->latest('id')]),
            'providers' => Payment::PROVIDERS,
            'packs' => Pack::where('is_active', true)->orderBy('type')->orderBy('name')->get(),
            'openTrials' => $openTrials,
            'selectedTrial' => $openTrials->firstWhere('id', $request->integer('trial')),
        ]);
    }

    public function pay(PaymentRequest $request, Order $order, OrderFulfillment $fulfillment): RedirectResponse
    {
        $this->ensureVisible($order);
        $payment = $request->safe()->only(['provider', 'provider_payment_id', 'failure_reason']);

        if ($request->validated('outcome') === 'failed') {
            $fulfillment->fail($order, $payment);

            return back()->with('warning', 'Failed attempt recorded. The order can still be paid.');
        }

        $fulfillment->pay($order, $payment, $request->safe()->only(['device_type', 'device_info', 'packs', 'trial_id']));

        return redirect()->route('orders.show', $order)->with('success', $order->type === 'renewal' ? 'Payment recorded and the line extended.' : 'Payment recorded and the line activated.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        Gate::authorize('orders.cancel');
        $this->ensureVisible($order);

        if (! $order->isPayable()) {
            return back()->with('error', "This order is {$order->status} and can’t be cancelled.");
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', "Order {$order->reference_number} cancelled.");
    }

    public function refund(Request $request, Order $order, OrderFulfillment $fulfillment): RedirectResponse
    {
        Gate::authorize('orders.refund');
        $this->ensureVisible($order);

        $fulfillment->refund($order, $request->boolean('cancel_line'));

        return back()->with('success', "Order {$order->reference_number} marked as refunded. Remember to return the money at the payment provider.");
    }
}
