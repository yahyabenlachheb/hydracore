<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PlanController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('plans.view');

        $search = trim((string) $request->query('q'));
        $status = $request->query('status');

        $plans = Plan::visibleTo($this->brandId())
            ->withCount(['subscriptions as active_lines_count' => fn ($query) => $query->where('status', 'active')])
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('sort_order')->orderBy('duration_months')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('plans.list', compact('plans', 'search', 'status'));
    }

    public function create(): View
    {
        Gate::authorize('plans.create');

        return view('plans.create', ['plan' => new Plan(['is_active' => true, 'currency' => 'USD', 'number_of_connections' => 1, 'sort_order' => 0])]);
    }

    public function store(PlanRequest $request): RedirectResponse
    {
        $plan = Plan::create([...$request->validated(), 'brand_id' => $this->brandId()]);

        return redirect()->route('plans.index')->with('success', "Plan “{$plan->name}” created.");
    }

    public function edit(Plan $plan): View
    {
        Gate::authorize('plans.update');
        $this->ensureVisible($plan);

        return view('plans.edit', compact('plan'));
    }

    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->ensureVisible($plan);
        $plan->update($request->validated());

        return redirect()->route('plans.index')->with('success', "Plan “{$plan->name}” updated. Existing orders keep the price they were sold at.");
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        Gate::authorize('plans.delete');
        $this->ensureVisible($plan);

        if ($plan->orders()->exists() || $plan->subscriptions()->exists()) {
            return back()->with('error', "“{$plan->name}” has been sold, so it can’t be deleted. Deactivate it to hide it from new orders.");
        }

        $plan->delete();

        return redirect()->route('plans.index')->with('success', "Plan “{$plan->name}” deleted.");
    }
}
