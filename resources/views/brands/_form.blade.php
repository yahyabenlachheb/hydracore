{{-- Shared brand fields for create and edit. Expects $brand. --}}
<div class="grid gap-6 p-6 sm:grid-cols-2">
    <x-field name="name" label="Name" :value="$brand->name" maxlength="150" required autofocus />
    <x-field name="website" label="Website" :value="$brand->website" placeholder="example.com" inputmode="url" hint="https:// is added if you leave it out." />
    <x-toggle class="sm:col-span-2" name="is_active" label="Active" :checked="$brand->is_active" hint="Inactive brands are hidden from the brand switcher." />
</div>
