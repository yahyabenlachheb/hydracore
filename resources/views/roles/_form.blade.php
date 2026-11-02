{{-- Shared role fields for create and edit. Expects $role and $permissionGroups. --}}
@php
    $input = 'mt-1.5 block w-full rounded-md border-0 px-3 py-2 text-sm ring-1 ring-inset focus:ring-2 focus:outline-none dark:bg-white/5';
    $ok = 'ring-neutral-300 focus:ring-accent-600 dark:ring-white/10';
    $bad = 'text-rose-900 ring-rose-300 focus:ring-rose-500 dark:bg-rose-500/5 dark:text-rose-300 dark:ring-rose-500/50';
    $selected = collect(old('permissions', $role->exists ? $role->permissions->pluck('id')->all() : []))->map(fn ($id) => (int) $id);
@endphp

<div class="grid gap-6 border-b border-neutral-200 p-6 sm:grid-cols-2 dark:border-white/10">
    <div>
        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Name <span class="text-rose-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" maxlength="100" required autofocus autocomplete="off"
               class="{{ $input }} {{ $errors->has('name') ? $bad : $ok }}">
        @error('name')<p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Description</label>
        <input type="text" id="description" name="description" value="{{ old('description', $role->description) }}" maxlength="255" autocomplete="off"
               class="{{ $input }} {{ $errors->has('description') ? $bad : $ok }}">
        @error('description')<p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
    </div>
</div>

<div class="p-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Permissions</h2>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                Users need at least one permission to sign in.
                <span data-permission-count class="font-medium text-neutral-700 dark:text-neutral-300"></span>
            </p>
        </div>
    </div>
    @error('permissions')<p class="mt-2 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror

    @if ($permissionGroups->isEmpty())
        <p class="mt-4 rounded-md border border-dashed border-neutral-300 p-4 text-sm text-neutral-500 dark:border-white/15 dark:text-neutral-400">No permissions exist yet.</p>
    @else
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($permissionGroups as $group => $permissions)
                <fieldset class="rounded-md ring-1 ring-neutral-200 ring-inset dark:ring-white/10" data-permission-group>
                    <legend class="sr-only">{{ $group }}</legend>
                    <label class="flex cursor-pointer items-center justify-between gap-3 border-b border-neutral-200 bg-neutral-50 px-4 py-2.5 dark:border-white/10 dark:bg-white/5">
                        <span class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $group }}</span>
                        <span class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                            All
                            <input type="checkbox" data-permission-group-toggle class="size-4 rounded-sm accent-accent-600">
                        </span>
                    </label>
                    <div class="space-y-1 p-2">
                        @foreach ($permissions as $permission)
                            <label @class([
                                'flex items-center gap-3 rounded-md px-2 py-1.5',
                                'cursor-pointer hover:bg-neutral-50 dark:hover:bg-white/5' => $permission->grantable,
                                'cursor-not-allowed opacity-50' => ! $permission->grantable,
                            ]) @unless ($permission->grantable) title="You can’t grant a permission you don’t have" @endunless>
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                       @checked($selected->contains($permission->id)) @disabled(! $permission->grantable)
                                       data-permission class="size-4 rounded-sm accent-accent-600">
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm text-neutral-700 dark:text-neutral-300">{{ $permission->name }}</span>
                                    <span class="block font-mono text-[11px] text-neutral-400 dark:text-neutral-500">{{ $permission->slug }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
    <script>
        // "All" checkbox per group, plus a live count of selected permissions.
        (() => {
            const boxes = [...document.querySelectorAll('[data-permission]')];
            const count = document.querySelector('[data-permission-count]');

            const refresh = () => {
                document.querySelectorAll('[data-permission-group]').forEach((group) => {
                    const items = [...group.querySelectorAll('[data-permission]:not(:disabled)')];
                    const toggle = group.querySelector('[data-permission-group-toggle]');
                    const checked = items.filter((box) => box.checked).length;
                    toggle.checked = items.length > 0 && checked === items.length;
                    toggle.indeterminate = checked > 0 && checked < items.length;
                    toggle.disabled = items.length === 0;
                });
                const total = boxes.filter((box) => box.checked).length;
                count.textContent = `${total} selected.`;
            };

            document.querySelectorAll('[data-permission-group-toggle]').forEach((toggle) =>
                toggle.addEventListener('change', () => {
                    toggle.closest('[data-permission-group]')
                        .querySelectorAll('[data-permission]:not(:disabled)')
                        .forEach((box) => { box.checked = toggle.checked; });
                    refresh();
                })
            );
            boxes.forEach((box) => box.addEventListener('change', refresh));
            refresh();
        })();
    </script>
@endpush
