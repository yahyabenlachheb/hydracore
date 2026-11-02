@extends('layouts.app')

@section('title', $order->reference_number)
@section('header', $order->reference_number)
@section('subheader', ucfirst($order->type) . ' order · ' . $order->created_at?->format('M j, Y H:i') . ' UTC')

@section('breadcrumbs')
    <x-crumbs :items="['Orders' => route('orders.index'), $order->reference_number => null]" />
@endsection

@section('actions')
    <x-status :status="$order->status" class="px-2.5 py-1 text-sm" />
    @if ($order->isPayable())
        @can('orders.cancel')
            <x-action-button :action="route('orders.cancel', $order)" danger confirm="Cancel order {{ $order->reference_number }}?">Cancel order</x-action-button>
        @endcan
    @endif
@endsection

@section('content')
    @php
        $card = 'rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10';
        $title = 'text-sm font-semibold text-neutral-900 dark:text-white';
        $trialPacks = old('packs', $selectedTrial?->packs->modelKeys() ?? []);
    @endphp

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-6">
            @error('order')
                <p class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">{{ $message }}</p>
            @enderror

            @if ($order->isPayable())
                @can('orders.pay')
                    <form method="POST" action="{{ route('orders.pay', $order) }}" class="{{ $card }}" data-payment-form>
                        @csrf
                        <div class="border-b border-neutral-200 px-6 py-4 dark:border-white/10">
                            <h2 class="{{ $title }}">Record a payment</h2>
                            <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">
                                For the full total of <x-money :amount="$order->total" :currency="$order->currency" />.
                                {{ $order->type === 'renewal' ? 'A successful payment extends the line by ' : 'A successful payment activates a line for ' }}{{ $order->duration_months }} {{ Str::plural('month', $order->duration_months) }}.
                            </p>
                        </div>
                        <div class="grid gap-6 p-6 sm:grid-cols-6">
                            <x-select wrapper="sm:col-span-2" name="outcome" label="Result" required :options="['succeeded' => 'Succeeded', 'failed' => 'Failed']" data-outcome />
                            <x-select wrapper="sm:col-span-2" name="provider" label="Provider" required :options="$providers" />
                            <x-field wrapper="sm:col-span-2" name="provider_payment_id" label="Transaction ID" maxlength="150" hint="From the provider. Blocks recording it twice." />

                            <div class="hidden sm:col-span-6" data-when-failed>
                                <x-field name="failure_reason" label="Why it failed" maxlength="255" />
                            </div>

                            @if ($order->type === 'new')
                                <div class="grid gap-6 border-t border-neutral-200 pt-6 sm:col-span-6 sm:grid-cols-6 dark:border-white/10" data-when-succeeded>
                                    <h3 class="text-xs font-semibold tracking-wide text-neutral-500 uppercase sm:col-span-6 dark:text-neutral-400">The line to activate</h3>
                                    @if ($openTrials->isNotEmpty())
                                        <x-select wrapper="sm:col-span-6" name="trial_id" label="Converted from trial" placeholder="None" :value="$selectedTrial?->id"
                                                  :options="$openTrials->mapWithKeys(fn ($trial) => [$trial->id => 'Trial #' . $trial->id . ' · ' . ucfirst($trial->status) . ' · ' . $trial->created_at?->format('M j, Y')])" />
                                    @endif
                                    <x-select wrapper="sm:col-span-3" name="device_type" label="Device" placeholder="Choose a device…" :options="App\Models\Subscription::DEVICE_TYPES" :value="$selectedTrial?->device_type" />
                                    <x-field wrapper="sm:col-span-3" name="device_info" label="Device details" maxlength="255" :value="$selectedTrial?->device_info" placeholder="MAC, app ID or model" />
                                    <x-pack-picker class="sm:col-span-6" :packs="$packs" :selected="$trialPacks" />
                                </div>
                            @endif
                        </div>
                        <div class="flex justify-end rounded-b-md border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-white/10 dark:bg-white/5">
                            <button type="submit" class="rounded-md bg-accent-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-accent-500">Record payment</button>
                        </div>
                    </form>
                @endcan
            @endif

            <section class="{{ $card }}">
                <h2 class="border-b border-neutral-200 px-5 py-3 {{ $title }} dark:border-white/10">Payment attempts</h2>
                @if ($order->payments->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">No payments recorded yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-white/10">
                            <thead class="bg-neutral-50 text-left text-xs font-semibold tracking-wide text-neutral-500 uppercase dark:bg-white/5 dark:text-neutral-400">
                                <tr><th class="px-5 py-2.5">Provider</th><th class="px-5 py-2.5">Transaction</th><th class="px-5 py-2.5">Status</th><th class="px-5 py-2.5 text-right">Amount</th><th class="px-5 py-2.5">Date</th></tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                                @foreach ($order->payments as $payment)
                                    <tr>
                                        <td class="px-5 py-2.5 text-neutral-900 dark:text-white">{{ App\Models\Payment::PROVIDERS[$payment->provider] ?? $payment->provider }}</td>
                                        <td class="px-5 py-2.5 font-mono text-xs text-neutral-600 dark:text-neutral-300">
                                            @can('payments.view')
                                                <a href="{{ route('payments.show', $payment) }}" class="hover:text-accent-600 dark:hover:text-accent-400">{{ $payment->provider_payment_id ?: "#{$payment->id}" }}</a>
                                            @else
                                                {{ $payment->provider_payment_id ?: "#{$payment->id}" }}
                                            @endcan
                                            @if ($payment->failure_reason)
                                                <p class="font-sans text-rose-600 dark:text-rose-400">{{ $payment->failure_reason }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-2.5"><x-status :status="$payment->status" /></td>
                                        <td class="px-5 py-2.5 text-right text-neutral-900 dark:text-white"><x-money :amount="$payment->amount" :currency="$payment->currency" /></td>
                                        <td class="px-5 py-2.5 whitespace-nowrap text-neutral-500 dark:text-neutral-400">{{ $payment->created_at?->format('M j, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            @if ($order->status === 'paid')
                @can('orders.refund')
                    <form method="POST" action="{{ route('orders.refund', $order) }}" class="{{ $card }} p-5" data-confirm="Mark {{ $order->reference_number }} as refunded? This only records the refund: return the money at the payment provider.">
                        @csrf
                        <h2 class="{{ $title }}">Refund</h2>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Records the refund here. The money itself is returned at the payment provider.</p>
                        @if ($order->subscription_id)
                            <label class="mt-3 flex items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                                <input type="checkbox" name="cancel_line" value="1" @checked($order->type === 'new') class="size-3.5 rounded border-neutral-300 accent-accent-600">
                                Also cancel subscription #{{ $order->subscription_id }}
                            </label>
                            @if ($order->type === 'renewal')
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Otherwise, adjust the line’s end date by hand: the renewal’s months stay on it.</p>
                            @endif
                        @endif
                        <button type="submit" class="mt-3 rounded-md bg-white px-3 py-2 text-sm font-semibold text-rose-600 shadow-sm ring-1 ring-rose-300 ring-inset hover:bg-rose-50 dark:bg-white/5 dark:text-rose-400 dark:ring-rose-500/40 dark:hover:bg-rose-500/10">Mark as refunded</button>
                    </form>
                @endcan
            @endif
        </div>

        <div class="space-y-6">
            <x-details title="Summary" :rows="[
                'Plan' => $order->plan_name,
                'Duration' => $order->duration_months . ' ' . Str::plural('month', $order->duration_months),
                'Subtotal' => number_format((float) $order->subtotal, 2) . ' ' . $order->currency,
                'Discount' => number_format((float) $order->discount, 2) . ' ' . $order->currency,
                'Total' => number_format((float) $order->total, 2) . ' ' . $order->currency,
                'Coupon' => $order->coupon_code,
                'Paid' => $order->paid_at?->format('M j, Y H:i'),
            ]" />

            @php
                $linkTo = fn (string $url, string $text) => new Illuminate\Support\HtmlString('<a class="text-accent-600 hover:text-accent-500 dark:text-accent-400" href="' . e($url) . '">' . e($text) . '</a>');
            @endphp
            <x-details title="Links" :rows="[
                'Customer' => $linkTo($order->customer->trashed() ? route('customers.index', ['archived' => 1]) : route('customers.show', $order->customer), $order->customer->full_name),
                'Email' => $order->customer->email,
                'Subscription' => $order->subscription_id ? $linkTo(route('subscriptions.show', $order->subscription_id), '#' . $order->subscription_id) : null,
                'IP address' => $order->ip_address,
            ]" />

            @if ($order->description)
                <div class="{{ $card }} px-5 py-3 text-sm text-neutral-700 dark:text-neutral-300">{{ $order->description }}</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Only the fields that matter for the chosen result are shown.
        (() => {
            const form = document.querySelector('[data-payment-form]');
            if (!form) return;
            const outcome = form.querySelector('[data-outcome]');
            const device = form.querySelector('[name="device_type"]');
            const sync = () => {
                const failed = outcome.value === 'failed';
                form.querySelector('[data-when-failed]').classList.toggle('hidden', !failed);
                form.querySelector('[data-when-succeeded]')?.classList.toggle('hidden', failed);
                if (device) device.required = !failed;
            };
            outcome.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
