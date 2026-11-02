<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Trial;
use App\Support\BrandContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Period => label. */
    public const PERIODS = ['today' => 'Today', '7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days'];

    public function __invoke(Request $request, BrandContext $context): View
    {
        $brandId = $this->brandId();
        $period = array_key_exists((string) $request->query('period'), self::PERIODS) ? $request->query('period') : '30d';

        $to = now();
        $from = match ($period) {
            'today' => $to->copy()->startOfDay(),
            '7d' => $to->copy()->subDays(6)->startOfDay(),
            '30d' => $to->copy()->subDays(29)->startOfDay(),
            '90d' => $to->copy()->subDays(89)->startOfDay(),
        };
        $previousFrom = $from->copy()->sub($from->diff($to));

        $data = ['period' => $period, 'from' => $from, 'to' => $to, 'brand' => $context->current()];

        // Each section only exists for users who may open the pages behind it.
        if (Gate::allows('orders.view')) {
            $paid = fn (Carbon $since, Carbon $until) => Order::visibleTo($brandId)->where('status', 'paid')->whereBetween('paid_at', [$since, $until]);

            // Amounts in different currencies can't be added up, so the figures are per currency.
            $revenue = $paid($from, $to)->selectRaw('currency, SUM(total) AS amount')->groupBy('currency')->orderByDesc('amount')->pluck('amount', 'currency');
            $currency = $revenue->keys()->first() ?? Order::visibleTo($brandId)->latest('id')->value('currency') ?? 'USD';

            $data += [
                'currency' => $currency,
                'revenue' => (float) ($revenue[$currency] ?? 0),
                'revenuePrevious' => (float) $paid($previousFrom, $from)->where('currency', $currency)->sum('total'),
                'otherCurrencies' => $revenue->except($currency),
                'paidOrders' => $paid($from, $to)->count(),
                'paidOrdersPrevious' => $paid($previousFrom, $from)->count(),
                'chart' => $this->revenueSeries($paid($from, $to)->where('currency', $currency)->get(['paid_at', 'total']), $from, $to, $period),
                'topPlans' => $paid($from, $to)->where('currency', $currency)
                    ->selectRaw('plan_name, COUNT(*) AS sold, SUM(total) AS amount')->groupBy('plan_name')->orderByDesc('amount')->limit(5)->get(),
                'recentOrders' => Order::visibleTo($brandId)->with('customer:id,full_name,email')->latest('id')->limit(6)->get(),
                'pendingOrders' => Order::visibleTo($brandId)->where('status', 'pending')->count(),
            ];
        }

        if (Gate::allows('subscriptions.view')) {
            $data += [
                'activeLines' => Subscription::visibleTo($brandId)->where('status', 'active')->count(),
                'expiringCount' => Subscription::visibleTo($brandId)->expiringWithin(7)->count(),
                'expiring' => Subscription::visibleTo($brandId)->expiringWithin(7)->with(['customer:id,full_name,email', 'plan:id,name'])->orderBy('expires_at')->limit(6)->get(),
            ];
        }

        if (Gate::allows('customers.view')) {
            $customers = fn (Carbon $since, Carbon $until) => Customer::query()->where('brand_id', $brandId)->whereBetween('created_at', [$since, $until])->count();
            $data += ['newCustomers' => $customers($from, $to), 'newCustomersPrevious' => $customers($previousFrom, $from)];
        }

        if (Gate::allows('trials.view')) {
            $trials = Trial::visibleTo($brandId)->whereBetween('created_at', [$from, $to]);
            $data += [
                'trialsStarted' => (clone $trials)->count(),
                'trialsConverted' => (clone $trials)->where('status', 'converted')->count(),
                'pendingTrials' => Trial::visibleTo($brandId)->where('status', 'pending')->count(),
            ];
        }

        if (Gate::allows('contacts.view')) {
            $data['newContacts'] = Contact::visibleTo($brandId)->where('status', 'new')->count();
        }

        return view('dashboard', $data);
    }

    /**
     * Revenue per hour (today) or per day, with empty buckets filled so the line is continuous.
     *
     * @param  Collection<int, Order>  $orders
     * @return list<array{label: string, revenue: float}>
     */
    private function revenueSeries(Collection $orders, Carbon $from, Carbon $to, string $period): array
    {
        [$unit, $key, $label] = $period === 'today' ? ['hour', 'Y-m-d H', 'H:00'] : ['day', 'Y-m-d', 'M j'];
        $totals = $orders->groupBy(fn (Order $order) => $order->paid_at->format($key))->map(fn ($group) => $group->sum('total'));

        $series = [];
        for ($at = $from->copy(); $at <= $to; $at->add(1, $unit)) {
            $series[] = ['label' => $at->format($label), 'revenue' => round((float) ($totals[$at->format($key)] ?? 0), 2)];
        }

        return $series;
    }
}
