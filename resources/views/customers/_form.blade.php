{{-- Shared customer fields for create and edit. Expects $customer. --}}
<div class="grid gap-6 p-6 sm:grid-cols-6">
    <x-field wrapper="sm:col-span-3" name="full_name" label="Full name" :value="$customer->full_name" maxlength="150" required autofocus />
    <x-field wrapper="sm:col-span-3" name="email" label="Email" type="email" :value="$customer->email" maxlength="190" required />
    <x-field wrapper="sm:col-span-3" name="phone" label="Phone" type="tel" :value="$customer->phone" maxlength="30" />
    <x-select wrapper="sm:col-span-3" name="country" label="Country" :options="array_combine(App\Support\Countries::CODES, App\Support\Countries::CODES)" :value="$customer->country" placeholder="Unknown" />
    <div class="sm:col-span-6">
        <label for="notes" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Internal notes</label>
        <textarea id="notes" name="notes" rows="4" maxlength="5000" class="mt-1.5 block w-full rounded-md border-0 px-3 py-2 text-sm ring-1 ring-neutral-300 ring-inset focus:ring-2 focus:ring-accent-600 focus:outline-none dark:bg-white/5 dark:ring-white/10">{{ old('notes', $customer->notes) }}</textarea>
        @error('notes')
            <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @else
            <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400">Only visible in this panel.</p>
        @enderror
    </div>
</div>
