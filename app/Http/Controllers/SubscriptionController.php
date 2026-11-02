<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Models\Customer;
use App\Models\Pack;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\Audit;
use App\Support\OrderFulfillment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    private const PER_PAGE = 25;

    /** action => statuses it can start from */
    private const TRANSITIONS = [
        'activate' => ['pending'],
        'suspend' => ['active'],
        'resume' => ['suspended'],
        'cancel' => ['pending', 'active', 'suspended', 'expired'],
    ];

    public function index(Request $request): View
    {
        Gate::authorize('subscriptions.view');
        $brandId = $this->brandId();

        $filters = [
            'q' => trim((string) $request->query('q')),
            'status' => array_key_exists((string) $request->query('status'), Subscription::STATUSES) ? $request->query('status') : null,
            'plan' => $request->integer('plan') ?: null,
            'expiring' => in_array($request->integer('expiring'), [7, 30], true) ? $request->integer('expiring') : null,
        ];

        $subscriptions = Subscription::visibleTo($brandId)
            ->with(['customer:id,full_name,email', 'plan:id,name'])
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('device_info', 'like', "%{$filters['q']}%")
                ->orWhereHas('customer', fn ($query) => $query->matching($filters['q']))))
            ->when($filters['status'], fn ($query, $status) => $query->where('status', $status))
            ->when($filters['plan'], fn ($query, $plan) => $query->where('plan_id', $plan))
            ->when($filters['expiring'], fn ($query, $days) => $query->expiringWithin($days)->orderBy('expires_at'))
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('subscriptions.list', [
            'subscriptions' => $subscriptions,
            'filters' => $filters,
            'plans' => Plan::visibleTo($brandId)->orderBy('sort_order')->orderBy('duration_months')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('subscriptions.create');

        return view('subscriptions.create', [
            'subscription' => new Subscription(['status' => 'active']),
            'customer' => $request->integer('customer') ? Customer::visibleTo($this->brandId())->find($request->integer('customer')) : null,
            ...$this->formOptions(),
        ]);
    }

    public function store(SubscriptionRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('packs');
        $plan = Plan::visibleTo($this->brandId())->findOrFail($data['plan_id']);

        // An active line needs dates; unless given, it runs for the plan's duration from now.
        if ($data['status'] === 'active') {
            $data['starts_at'] ??= now();
            $data['expires_at'] ??= OrderFulfillment::addMonths(now(), $plan->duration_months);
        }

        $subscription = DB::transaction(function () use ($data, $request) {
            $subscription = Subscription::create($data);
            $subscription->packs()->sync($request->validated('packs'));

            return $subscription;
        });

        return redirect()->route('subscriptions.show', $subscription)->with('success', 'Subscription created.');
    }

    public function show(Subscription $subscription): View
    {
        Gate::authorize('subscriptions.view');
        $this->ensureVisible($subscription);

        return view('subscriptions.show', [
            'subscription' => $subscription->load(['customer', 'plan', 'packs', 'orders' => fn ($query) => $query->latest('id')]),
            'transitions' => array_keys(array_filter(self::TRANSITIONS, fn (array $from) => in_array($subscription->status, $from, true))),
        ]);
    }

    public function edit(Subscription $subscription): View
    {
        Gate::authorize('subscriptions.update');
        $this->ensureVisible($subscription);

        return view('subscriptions.edit', [
            'subscription' => $subscription->load(['customer', 'packs:id']),
            'customer' => $subscription->customer,
            ...$this->formOptions($subscription),
        ]);
    }

    public function update(SubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $this->ensureVisible($subscription);

        DB::transaction(function () use ($request, $subscription) {
            $subscription->update($request->safe()->except(['packs', 'status', 'customer_id']));

            $old = $subscription->packs()->orderBy('code')->pluck('code')->all();
            $subscription->packs()->sync($request->validated('packs'));
            $new = $subscription->packs()->orderBy('code')->pluck('code')->all();

            // Pivot syncs don't fire model events, so log pack changes explicitly.
            if ($old !== $new) {
                Audit::record($subscription, 'update', ['packs' => $old], ['packs' => $new], "Changed packs of subscription #{$subscription->id}");
            }
        });

        return redirect()->route('subscriptions.show', $subscription)->with('success', 'Subscription updated.');
    }

    public function status(Request $request, Subscription $subscription): RedirectResponse
    {
        Gate::authorize('subscriptions.update');
        $this->ensureVisible($subscription);

        $action = (string) $request->input('action');

        if (! in_array($subscription->status, self::TRANSITIONS[$action] ?? [], true)) {
            return back()->with('error', "A {$subscription->status} subscription can’t be changed this way.");
        }

        $subscription->update(match ($action) {
            'activate' => [
                'status' => 'active',
                'starts_at' => $subscription->starts_at ?? now(),
                'expires_at' => $subscription->expires_at ?? OrderFulfillment::addMonths(now(), $subscription->plan->duration_months),
            ],
            'suspend' => ['status' => 'suspended'],
            // Suspended time isn't given back; a line whose end date passed meanwhile just expires.
            'resume' => ['status' => $subscription->expires_at?->isPast() ? 'expired' : 'active'],
            'cancel' => ['status' => 'cancelled', 'cancelled_at' => now()],
        });

        return back()->with('success', 'Subscription is now ' . $subscription->status . '.');
    }

    /**
     * Inactive plans and packs are offered only when the subscription already uses them.
     *
     * @return array{plans: Collection<int, Plan>, packs: Collection<int, Pack>}
     */
    private function formOptions(?Subscription $subscription = null): array
    {
        $packIds = $subscription?->packs->modelKeys() ?? [];

        return [
            'plans' => Plan::visibleTo($this->brandId())
                ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', $subscription?->plan_id))
                ->orderBy('sort_order')->orderBy('duration_months')->get(),
            'packs' => Pack::query()
                ->where(fn ($query) => $query->where('is_active', true)->orWhereIn('id', $packIds))
                ->orderBy('type')->orderBy('name')->get(),
        ];
    }
}
