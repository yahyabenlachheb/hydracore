<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        Gate::authorize('roles.view');

        $search = trim((string) $request->query('q'));

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('roles.list', compact('roles', 'search'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('roles.create');

        return view('roles.create', [
            'role' => new Role,
            'permissionGroups' => $this->permissionGroups($request->user()),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = DB::transaction(function () use ($request) {
            $role = Role::create($request->safe()->only(['name', 'description']));
            $this->syncPermissions($request->user(), $role, $request->input('permissions'));

            return $role;
        });

        return redirect()->route('roles.index')->with('success', "Role “{$role->name}” created.");
    }

    public function edit(Request $request, Role $role): View
    {
        Gate::authorize('roles.update');

        return view('roles.edit', [
            'role' => $role->load('permissions:id')->loadCount('users'),
            'permissionGroups' => $this->permissionGroups($request->user()),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        DB::transaction(function () use ($request, $role) {
            $role->update($request->safe()->only(['name', 'description']));
            $this->syncPermissions($request->user(), $role, $request->input('permissions'));
        });

        return redirect()->route('roles.index')->with('success', "Role “{$role->name}” updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('roles.delete');

        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role “{$role->name}” deleted.");
    }

    /**
     * All permissions grouped by slug prefix ("brands.create" → "Brands"), each flagged with
     * whether the acting user may grant it.
     *
     * @return Collection<string, Collection<int, Permission>>
     */
    private function permissionGroups(User $actor): Collection
    {
        $grantable = $this->grantablePermissionIds($actor);

        return Permission::orderBy('slug')->get()
            ->each(fn (Permission $permission) => $permission->setAttribute('grantable', $grantable->contains($permission->id)))
            ->groupBy(fn (Permission $permission) => Str::headline(Str::before($permission->slug, '.')));
    }

    /**
     * Non-super admins can only grant permissions they hold, so a role editor can't escalate
     * their own access through a role they belong to.
     *
     * @return Collection<int, int>
     */
    private function grantablePermissionIds(User $actor): Collection
    {
        return ($actor->isSuperAdmin() ? Permission::query() : Permission::whereIn('slug', $actor->permissionSlugs()))->pluck('id');
    }

    /**
     * @param  array<int, int|string>  $submitted
     */
    private function syncPermissions(User $actor, Role $role, array $submitted): void
    {
        $grantable = $this->grantablePermissionIds($actor);
        $old = $role->permissions()->orderBy('slug')->pluck('slug')->all();

        $role->permissions()->sync(
            $grantable->intersect($submitted)
                ->merge($role->permissions()->pluck('permissions.id')->diff($grantable))
                ->unique()->values()
        );

        // Pivot syncs don't fire model events, so log permission changes explicitly.
        $new = $role->permissions()->orderBy('slug')->pluck('slug')->all();

        if ($old !== $new) {
            Audit::record($role, 'update', ['permissions' => $old], ['permissions' => $new], "Changed permissions of role “{$role->name}”");
        }
    }
}
