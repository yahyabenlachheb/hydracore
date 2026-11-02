<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PermissionController extends Controller
{
    private const PER_PAGE = 25;

    public function index(Request $request): View
    {
        Gate::authorize('permissions.view');

        $search = trim((string) $request->query('q'));

        $permissions = Permission::query()
            ->withCount('roles')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")))
            ->orderBy('slug')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('permissions.list', compact('permissions', 'search'));
    }

    public function create(): View
    {
        Gate::authorize('permissions.create');

        return view('permissions.create', ['permission' => new Permission]);
    }

    public function store(PermissionRequest $request): RedirectResponse
    {
        $permission = Permission::create($request->safe()->only(['name', 'slug']));

        return redirect()->route('permissions.index')->with('success', "Permission “{$permission->slug}” created.");
    }

    public function edit(Permission $permission): View
    {
        Gate::authorize('permissions.update');

        return view('permissions.edit', ['permission' => $permission->loadCount('roles')]);
    }

    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update($request->safe()->only($permission->isSystem() ? ['name'] : ['name', 'slug']));

        return redirect()->route('permissions.index')->with('success', "Permission “{$permission->slug}” updated.");
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        Gate::authorize('permissions.delete');

        if ($permission->isSystem()) {
            return back()->with('error', "“{$permission->slug}” is used by the app and can’t be deleted.");
        }

        $permission->delete();

        return redirect()->route('permissions.index')->with('success', "Permission “{$permission->slug}” deleted.");
    }
}
