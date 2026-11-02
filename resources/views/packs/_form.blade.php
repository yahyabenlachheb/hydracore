{{-- Shared pack fields for create and edit. Expects $pack. --}}
<div class="grid gap-6 p-6 sm:grid-cols-6">
    <x-field wrapper="sm:col-span-6" name="name" label="Name" :value="$pack->name" maxlength="150" required autofocus placeholder="France" />
    <x-select wrapper="sm:col-span-3" name="type" label="Type" :options="App\Models\Pack::TYPES" :value="$pack->type" required />
    <x-field wrapper="sm:col-span-3" name="code" label="Code" :value="$pack->code" maxlength="100" required class="font-mono uppercase" placeholder="FR" hint="The value your website and panel use." />
    <x-toggle class="sm:col-span-6" name="is_active" label="Active" :checked="$pack->is_active" hint="Inactive packs can’t be added to new subscriptions." />
</div>
