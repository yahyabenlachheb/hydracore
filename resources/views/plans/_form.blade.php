{{-- Shared plan fields for create and edit. Expects $plan. --}}
<div class="grid gap-6 p-6 sm:grid-cols-6">
    <x-field wrapper="sm:col-span-6" name="name" label="Name" :value="$plan->name" maxlength="100" required autofocus placeholder="3 Months" />
    <x-field wrapper="sm:col-span-2" name="duration_months" label="Duration (months)" type="number" min="1" max="120" :value="$plan->duration_months" required />
    <x-field wrapper="sm:col-span-2" name="price" label="Price" type="number" min="0" step="0.01" :value="$plan->price" required />
    <x-field wrapper="sm:col-span-2" name="currency" label="Currency" :value="$plan->currency" maxlength="3" required class="uppercase" hint="Three-letter code, such as USD." />
    <x-field wrapper="sm:col-span-3" name="number_of_connections" label="Connections" type="number" min="1" max="255" :value="$plan->number_of_connections" required hint="Screens allowed at the same time." />
    <x-field wrapper="sm:col-span-3" name="sort_order" label="Sort order" type="number" :value="$plan->sort_order" hint="Lower numbers are listed first." />
    <x-toggle class="sm:col-span-6" name="is_active" label="Active" :checked="$plan->is_active" hint="Inactive plans are hidden from new orders but stay on existing ones." />
</div>
