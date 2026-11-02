<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BrandController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        Gate::authorize('brands.view');

        $search = trim((string) $request->query('q'));
        $status = $request->query('status');

        $brands = $this->visibleBrands($request->user())
            ->withCount(['users', 'plans', 'customers'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('website', 'like', "%{$search}%")))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('brands.list', compact('brands', 'search', 'status'));
    }

    public function create(): View
    {
        Gate::authorize('brands.create');

        return view('brands.create', ['brand' => new Brand(['is_active' => true])]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        $brand = Brand::create($request->validated());

        // Non-admin creators must be able to see the brand they just made.
        if (! $request->user()->isSuperAdmin()) {
            $brand->users()->attach($request->user());
        }

        return redirect()->route('brands.index')->with('success', "Brand “{$brand->name}” created.");
    }

    public function edit(Request $request, Brand $brand): View
    {
        Gate::authorize('brands.update');
        $this->ensureBrandVisible($request->user(), $brand);

        return view('brands.edit', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->ensureBrandVisible($request->user(), $brand);

        $brand->update($request->validated());

        return redirect()->route('brands.index')->with('success', "Brand “{$brand->name}” updated.");
    }

    public function destroy(Request $request, Brand $brand): RedirectResponse
    {
        Gate::authorize('brands.delete');
        $this->ensureBrandVisible($request->user(), $brand);

        // Plans restrict the delete; the rest would silently lose their brand and become shared.
        $inUse = collect([
            'plans' => $brand->plans(),
            'customers' => $brand->customers(),
            'orders' => $brand->orders(),
            'trials' => $brand->trials(),
            'contact messages' => $brand->contacts(),
        ])->filter(fn ($relation) => $relation->exists())->keys();

        if ($inUse->isNotEmpty()) {
            return back()->with('error', "“{$brand->name}” still has {$inUse->join(', ', ' and ')}. Deactivate it instead.");
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', "Brand “{$brand->name}” deleted.");
    }

    /**
     * Super admins manage every brand; other users only the brands assigned to them.
     *
     * @return Builder<Brand>|BelongsToMany<Brand, User>
     */
    private function visibleBrands(User $user): Builder|BelongsToMany
    {
        return $user->isSuperAdmin() ? Brand::query() : $user->brands();
    }

    private function ensureBrandVisible(User $user, Brand $brand): void
    {
        abort_unless($user->isSuperAdmin() || $user->brands()->whereKey($brand->id)->exists(), 404);
    }
}
