<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Trial;
use App\Support\Countries;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('customers.view');

        $filters = [
            'q' => trim((string) $request->query('q')),
            'line' => in_array($request->query('line'), ['active', 'none'], true) ? $request->query('line') : null,
            'country' => in_array($request->query('country'), Countries::CODES, true) ? $request->query('country') : null,
            'archived' => $request->boolean('archived'),
        ];
        $activeLines = fn ($query) => $query->where('status', 'active');

        $customers = Customer::visibleTo($this->brandId())
            ->when($filters['archived'], fn ($query) => $query->onlyTrashed())
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('full_name', 'like', "%{$filters['q']}%")
                ->orWhere('email', 'like', "%{$filters['q']}%")
                ->orWhere('phone', 'like', "%{$filters['q']}%")))
            ->when($filters['line'] === 'active', fn ($query) => $query->whereHas('subscriptions', $activeLines))
            ->when($filters['line'] === 'none', fn ($query) => $query->whereDoesntHave('subscriptions', $activeLines))
            ->when($filters['country'], fn ($query, $country) => $query->where('country', $country))
            ->withCount(['subscriptions as active_lines_count' => $activeLines])
            ->withCount('orders')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('customers.list', [
            'customers' => $customers,
            'filters' => $filters,
            'countries' => Customer::visibleTo($this->brandId())->whereNotNull('country')->distinct()->orderBy('country')->pluck('country'),
        ]);
    }

    /**
     * The customer picker on the order, subscription and trial forms.
     */
    public function search(Request $request): JsonResponse
    {
        abort_unless(Gate::any(['customers.view', 'orders.create', 'subscriptions.create', 'trials.create']), 403);

        $q = trim((string) $request->query('q'));

        return response()->json(Customer::visibleTo($this->brandId())
            ->when($q !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('full_name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")))
            ->orderBy('full_name')->limit(10)->get(['id', 'full_name', 'email']));
    }

    public function create(): View
    {
        Gate::authorize('customers.create');

        return view('customers.create', ['customer' => new Customer]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create([...$request->validated(), 'brand_id' => $this->brandId()]);

        return redirect()->route('customers.show', $customer)->with('success', "Customer {$customer->email} added.");
    }

    public function show(Customer $customer): View
    {
        Gate::authorize('customers.view');
        $this->ensureVisible($customer);
        $brandId = $this->brandId();

        return view('customers.show', [
            'customer' => $customer->load('brand:id,name'),
            'subscriptions' => Subscription::visibleTo($brandId)->where('customer_id', $customer->id)->with('plan:id,name')->latest('id')->get(),
            'orders' => Order::visibleTo($brandId)->where('customer_id', $customer->id)->latest('id')->limit(20)->get(),
            'trials' => Trial::visibleTo($brandId)->where('customer_id', $customer->id)->latest('id')->limit(20)->get(),
            'contacts' => Contact::visibleTo($brandId)->where('customer_id', $customer->id)->latest('id')->limit(20)->get(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        Gate::authorize('customers.update');
        $this->ensureVisible($customer);

        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->ensureVisible($customer);
        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)->with('success', "Customer {$customer->email} updated.");
    }

    /**
     * Archives (soft-deletes): orders and payments must keep pointing at the customer.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('customers.delete');
        $this->ensureVisible($customer);

        if ($customer->subscriptions()->whereIn('status', ['pending', 'active', 'suspended'])->exists()) {
            return back()->with('error', "{$customer->email} still has a running subscription. Cancel it first.");
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', "Customer {$customer->email} archived.");
    }

    public function restore(Customer $customer): RedirectResponse
    {
        Gate::authorize('customers.delete');
        abort_unless(Customer::onlyTrashed()->visibleTo($this->brandId())->whereKey($customer->id)->exists(), 404);

        $customer->restore();

        return redirect()->route('customers.show', $customer)->with('success', "Customer {$customer->email} restored.");
    }
}
