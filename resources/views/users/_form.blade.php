{{-- Shared user fields for create and edit. Expects $user, $assignableRoles, $assignableBrands. --}}
@php
    $input = 'mt-1.5 block w-full rounded-md border-0 px-3 py-2 text-sm ring-1 ring-inset focus:ring-2 focus:outline-none dark:bg-white/5';
    $ok = 'ring-neutral-300 focus:ring-accent-600 dark:ring-white/10';
    $bad = 'text-rose-900 ring-rose-300 focus:ring-rose-500 dark:bg-rose-500/5 dark:text-rose-300 dark:ring-rose-500/50';
    $actor = auth()->user();
    $isSelf = $user->exists && $user->is($actor);
    $selectedRoles = collect(old('roles', $user->exists ? $user->roles->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
    $selectedBrands = collect(old('brands', $user->exists ? $user->brands->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
    $section = 'border-t border-neutral-200 px-6 py-6 first:border-t-0 dark:border-white/10';
@endphp

{{-- Account --}}
<div class="{{ $section }}">
    <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Account</h2>
    <div class="mt-4 grid gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Full name <span class="text-rose-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="150" required autofocus autocomplete="off"
                   class="{{ $input }} {{ $errors->has('name') ? $bad : $ok }}">
            @error('name')<p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="username" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Username <span class="text-rose-500">*</span></label>
            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" maxlength="100" required autocomplete="off" autocapitalize="none" spellcheck="false"
                   class="{{ $input }} {{ $errors->has('username') ? $bad : $ok }}">
            @error('username')
                <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400">Used to sign in. Letters, numbers, dashes and underscores.</p>
            @enderror
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Email <span class="text-rose-500">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" maxlength="190" required autocomplete="off"
                   class="{{ $input }} {{ $errors->has('email') ? $bad : $ok }}">
            @error('email')<p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Password --}}
<div class="{{ $section }}">
    <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Password</h2>
    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
        {{ $user->exists ? 'Leave blank to keep the current password.' : 'At least 8 characters.' }}
    </p>
    <div class="mt-4 grid gap-6 sm:grid-cols-2">
        <div>
            <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                {{ $user->exists ? 'New password' : 'Password' }} @unless ($user->exists)<span class="text-rose-500">*</span>@endunless
            </label>
            <input type="password" id="password" name="password" autocomplete="new-password" @unless ($user->exists) required @endunless
                   class="{{ $input }} {{ $errors->has('password') ? $bad : $ok }}">
            @error('password')<p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                   class="{{ $input }} {{ $ok }}">
        </div>
    </div>
</div>

{{-- Status --}}
<div class="{{ $section }} space-y-3">
    <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Status</h2>

    <label @class(['flex items-start justify-between gap-6 rounded-md p-4 ring-1 ring-neutral-200 ring-inset dark:ring-white/10', 'cursor-pointer' => ! $isSelf, 'opacity-60' => $isSelf])>
        <span>
            <span class="block text-sm font-medium text-neutral-900 dark:text-white">Active</span>
            <span class="block text-xs text-neutral-500 dark:text-neutral-400">
                {{ $isSelf ? 'You can’t deactivate your own account.' : 'Inactive users can’t sign in.' }}
            </span>
        </span>
        <input type="hidden" name="is_active" value="{{ $isSelf ? 1 : 0 }}">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) @disabled($isSelf) class="peer sr-only">
        <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-neutral-200 transition-colors after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-accent-600 peer-checked:after:tranneutral-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500 peer-focus-visible:ring-offset-2 dark:bg-white/10 dark:peer-focus-visible:ring-offset-neutral-900"></span>
    </label>
    @error('is_active')<p class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

    @if ($actor->isSuperAdmin())
        <label @class(['flex items-start justify-between gap-6 rounded-md p-4 ring-1 ring-neutral-200 ring-inset dark:ring-white/10', 'cursor-pointer' => ! $isSelf, 'opacity-60' => $isSelf])>
            <span>
                <span class="block text-sm font-medium text-neutral-900 dark:text-white">Super admin</span>
                <span class="block text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $isSelf ? 'You can’t remove your own super admin access.' : 'Full access to everything and every brand. Roles and brands below are ignored.' }}
                </span>
            </span>
            @if ($isSelf)
                <input type="hidden" name="is_super_admin" value="{{ $user->is_super_admin ? 1 : 0 }}">
            @else
                <input type="hidden" name="is_super_admin" value="0">
            @endif
            <input type="checkbox" name="is_super_admin" value="1" @checked(old('is_super_admin', $user->is_super_admin)) @disabled($isSelf) data-super-admin-toggle class="peer sr-only">
            <span class="relative mt-0.5 h-6 w-11 shrink-0 rounded-full bg-neutral-200 transition-colors after:absolute after:top-0.5 after:left-0.5 after:size-5 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:bg-accent-600 peer-checked:after:tranneutral-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-accent-500 peer-focus-visible:ring-offset-2 dark:bg-white/10 dark:peer-focus-visible:ring-offset-neutral-900"></span>
        </label>
        @error('is_super_admin')<p class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    @endif
</div>

{{-- Access --}}
<div class="{{ $section }}" data-access-section>
    <div class="grid gap-8 lg:grid-cols-2">
        <div>
            <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Roles</h2>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">What the user can do. They need at least one role with permissions to sign in.</p>
            <div class="mt-4 space-y-2">
                @forelse ($assignableRoles as $role)
                    <label class="flex cursor-pointer items-center gap-3 rounded-md p-3 ring-1 ring-neutral-200 ring-inset hover:bg-neutral-50 has-checked:bg-accent-50 has-checked:ring-accent-500/40 dark:ring-white/10 dark:hover:bg-white/5 dark:has-checked:bg-accent-500/10">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($selectedRoles->contains($role->id)) class="size-4 rounded-sm accent-accent-600">
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-neutral-900 dark:text-white">{{ $role->name }}</span>
                            @if ($role->description)
                                <span class="block truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $role->description }}</span>
                            @endif
                        </span>
                        <span @class([
                            'shrink-0 text-xs',
                            'text-neutral-500 dark:text-neutral-400' => $role->permissions_count > 0,
                            'text-amber-600 dark:text-amber-400' => $role->permissions_count === 0,
                        ])>{{ $role->permissions_count }} {{ Str::plural('permission', $role->permissions_count) }}</span>
                    </label>
                @empty
                    <p class="rounded-md border border-dashed border-neutral-300 p-4 text-sm text-neutral-500 dark:border-white/15 dark:text-neutral-400">
                        No roles you can assign yet.
                        @can('roles.create')<a href="{{ route('roles.create') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Create a role</a>@endcan
                    </p>
                @endforelse
            </div>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Brands</h2>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Which brands the user can work in.</p>
            <div class="mt-4 space-y-2">
                @forelse ($assignableBrands as $brand)
                    <label class="flex cursor-pointer items-center gap-3 rounded-md p-3 ring-1 ring-neutral-200 ring-inset hover:bg-neutral-50 has-checked:bg-accent-50 has-checked:ring-accent-500/40 dark:ring-white/10 dark:hover:bg-white/5 dark:has-checked:bg-accent-500/10">
                        <input type="checkbox" name="brands[]" value="{{ $brand->id }}" @checked($selectedBrands->contains($brand->id)) class="size-4 rounded-sm accent-accent-600">
                        <x-brand-avatar :brand="$brand" variant="solid" class="size-8 rounded-md text-xs" />
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium text-neutral-900 dark:text-white">{{ $brand->name }}</span>
                            <span class="block truncate text-xs text-neutral-500 dark:text-neutral-400">{{ parse_url($brand->website ?? '', PHP_URL_HOST) ?: ' ' }}</span>
                        </span>
                        @unless ($brand->is_active)
                            <span class="shrink-0 rounded bg-neutral-100 px-1.5 py-0.5 text-[10px] font-semibold text-neutral-500 uppercase dark:bg-white/10 dark:text-neutral-400">Inactive</span>
                        @endunless
                    </label>
                @empty
                    <p class="rounded-md border border-dashed border-neutral-300 p-4 text-sm text-neutral-500 dark:border-white/15 dark:text-neutral-400">No brands you can assign yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Roles and brands don't apply to super admins; dim them while the toggle is on.
        (() => {
            const toggle = document.querySelector('[data-super-admin-toggle]');
            const section = document.querySelector('[data-access-section]');
            const sync = () => section?.classList.toggle('opacity-40', !!toggle?.checked);
            toggle?.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
