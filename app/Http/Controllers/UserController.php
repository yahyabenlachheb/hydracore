<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Brand;
use App\Models\Role;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        Gate::authorize('users.view');

        $search = trim((string) $request->query('q'));
        $status = $request->query('status');
        $roleId = $request->integer('role') ?: null;

        $users = User::query()
            ->with('roles:id,name')
            ->withCount('brands')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'super-admin', fn ($query) => $query->where('is_super_admin', true))
            ->when($roleId, fn ($query) => $query->whereHas('roles', fn ($query) => $query->whereKey($roleId)))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('users.list', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(['id', 'name']),
            'search' => $search,
            'status' => $status,
            'roleId' => $roleId,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('users.create');

        return view('users.create', [
            'user' => new User(['is_active' => true]),
            ...$this->formOptions($request->user()),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $actor = $request->user();

        $user = DB::transaction(function () use ($request, $actor) {
            $user = User::create([
                ...$request->safe()->only(['name', 'username', 'email', 'password', 'is_active']),
                'is_super_admin' => $actor->isSuperAdmin() && $request->boolean('is_super_admin'),
            ]);

            $this->syncAccess($actor, $user, $request);

            return $user;
        });

        return redirect()->route('users.index')->with('success', "User “{$user->name}” created.");
    }

    public function edit(Request $request, User $user): View
    {
        Gate::authorize('users.update');
        $this->ensureCanManage($request->user(), $user);

        return view('users.edit', [
            'user' => $user->load('roles:id', 'brands:id'),
            ...$this->formOptions($request->user()),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->ensureCanManage($actor, $user);

        $data = $request->safe()->only(['name', 'username', 'email', 'is_active']);
        $data['is_super_admin'] = $actor->isSuperAdmin() ? $request->boolean('is_super_admin') : $user->is_super_admin;

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
        }

        if ($user->is($actor) && (! $data['is_active'] || ($user->is_super_admin && ! $data['is_super_admin']))) {
            throw ValidationException::withMessages([
                'is_active' => 'You can’t deactivate your own account or remove your own super admin access.',
            ]);
        }

        if ($this->wouldRemoveLastSuperAdmin($user, $data['is_super_admin'] && $data['is_active'])) {
            throw ValidationException::withMessages([
                'is_super_admin' => 'This is the last active super admin. Make someone else a super admin first.',
            ]);
        }

        DB::transaction(function () use ($user, $data, $actor, $request) {
            $user->update($data);
            $this->syncAccess($actor, $user, $request);
        });

        return redirect()->route('users.index')->with('success', "User “{$user->name}” updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('users.delete');
        $actor = $request->user();
        $this->ensureCanManage($actor, $user);

        if ($user->is($actor)) {
            return back()->with('error', 'You can’t delete your own account.');
        }

        if ($this->wouldRemoveLastSuperAdmin($user, false)) {
            return back()->with('error', "“{$user->name}” is the last active super admin and can’t be deleted.");
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', "User “{$user->name}” deleted.");
    }

    /**
     * Roles and brands the acting user may hand out.
     *
     * @return array{assignableRoles: Collection<int, Role>, assignableBrands: Collection<int, Brand>}
     */
    private function formOptions(User $actor): array
    {
        return [
            'assignableRoles' => $this->assignableRoles($actor)->withCount('permissions')->orderBy('name')->get(),
            'assignableBrands' => ($actor->isSuperAdmin() ? Brand::query() : $actor->brands())->orderBy('name')->get(),
        ];
    }

    /**
     * Non-super admins may only grant roles whose permissions they hold themselves, so nobody can
     * use the users screen to give themselves (or a friend) more access than they have.
     */
    private function assignableRoles(User $actor)
    {
        return $actor->isSuperAdmin()
            ? Role::query()
            : Role::whereDoesntHave('permissions', fn ($query) => $query->whereNotIn('slug', $actor->permissionSlugs()));
    }

    /**
     * Syncs the submitted roles and brands, limited to what the actor may assign. Anything the
     * actor can't see or grant is left untouched rather than silently removed.
     */
    private function syncAccess(User $actor, User $user, UserRequest $request): void
    {
        $oldRoles = $user->roles()->orderBy('name')->pluck('name')->all();
        $oldBrands = $user->brands()->orderBy('name')->pluck('name')->all();

        $assignableRoleIds = $this->assignableRoles($actor)->pluck('id');
        $user->roles()->sync(
            $assignableRoleIds->intersect($request->input('roles'))
                ->merge($user->roles()->pluck('roles.id')->diff($assignableRoleIds))
                ->unique()->values()
        );

        $assignableBrandIds = ($actor->isSuperAdmin() ? Brand::query() : $actor->brands())->pluck('brands.id');
        $user->brands()->sync(
            $assignableBrandIds->intersect($request->input('brands'))
                ->merge($user->brands()->pluck('brands.id')->diff($assignableBrandIds))
                ->unique()->values()
        );

        // Pivot syncs don't fire model events, so log access changes explicitly.
        $newRoles = $user->roles()->orderBy('name')->pluck('name')->all();
        $newBrands = $user->brands()->orderBy('name')->pluck('name')->all();

        if ($oldRoles !== $newRoles) {
            Audit::record($user, 'update', ['roles' => $oldRoles], ['roles' => $newRoles], "Changed roles of user “{$user->username}”");
        }

        if ($oldBrands !== $newBrands) {
            Audit::record($user, 'update', ['brands' => $oldBrands], ['brands' => $newBrands], "Changed brands of user “{$user->username}”");
        }
    }

    /**
     * Only super admins may edit or delete super admins.
     */
    private function ensureCanManage(User $actor, User $user): void
    {
        abort_if($user->isSuperAdmin() && ! $actor->isSuperAdmin(), 403);
    }

    private function wouldRemoveLastSuperAdmin(User $user, bool $staysActiveSuperAdmin): bool
    {
        if (! $user->is_super_admin || ! $user->is_active || $staysActiveSuperAdmin) {
            return false;
        }

        return ! User::where('is_super_admin', true)->where('is_active', true)->whereKeyNot($user->id)->exists();
    }
}
