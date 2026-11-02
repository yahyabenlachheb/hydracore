@props(['packs', 'selected' => []])
{{-- Chips for choosing channel and VOD packs, submitted as packs[]. --}}
@php($selected = array_map('intval', old('packs', $selected)))
<fieldset {{ $attributes }}>
    <legend class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Packs</legend>
    @if ($packs->isEmpty())
        <p class="mt-1.5 text-sm text-neutral-500 dark:text-neutral-400">No packs yet.
            @can('packs.create')<a href="{{ route('packs.create') }}" class="font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Create one</a>@endcan
        </p>
    @else
        <div class="mt-2 space-y-3">
            @foreach ($packs->groupBy('type') as $type => $group)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="w-16 text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:text-neutral-400">{{ App\Models\Pack::TYPES[$type] ?? $type }}</span>
                    @foreach ($group as $pack)
                        <label class="flex cursor-pointer items-center gap-2 rounded-md px-3 py-1.5 text-sm text-neutral-700 ring-1 ring-neutral-300 ring-inset select-none hover:bg-neutral-50 has-checked:bg-accent-50 has-checked:text-accent-700 has-checked:ring-2 has-checked:ring-accent-500 dark:text-neutral-300 dark:ring-white/10 dark:hover:bg-white/5 dark:has-checked:bg-accent-500/10 dark:has-checked:text-accent-300">
                            <input type="checkbox" name="packs[]" value="{{ $pack->id }}" @checked(in_array($pack->id, $selected, true)) class="size-3.5 rounded border-neutral-300 accent-accent-600">
                            {{ $pack->name }}{{ $pack->is_active ? '' : ' (inactive)' }}
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
    @error('packs.*')<p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>@enderror
</fieldset>
