@extends('layouts.app')

@section('title', "Payment #{$payment->id}")
@section('header', "Payment #{$payment->id}")
@section('subheader', 'Recorded ' . $payment->created_at?->format('M j, Y H:i') . ' UTC')

@section('breadcrumbs')
    <x-crumbs :items="['Payments' => route('payments.index'), '#' . $payment->id => null]" />
@endsection

@section('actions')
    <x-status :status="$payment->status" class="px-2.5 py-1 text-sm" />
@endsection

@section('content')
    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10">
            <h2 class="border-b border-neutral-200 px-5 py-3 text-sm font-semibold text-neutral-900 dark:border-white/10 dark:text-white">Provider reply</h2>
            @if ($payment->payload)
                <pre class="max-h-[32rem] overflow-auto px-5 py-4 font-mono text-xs text-neutral-700 dark:text-neutral-300">{{ json_encode($payment->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
            @else
                <p class="px-5 py-8 text-center text-sm text-neutral-500 dark:text-neutral-400">Recorded by hand, so there’s no provider reply.</p>
            @endif
        </section>

        <x-details title="Details" :rows="[
            'Provider' => App\Models\Payment::PROVIDERS[$payment->provider] ?? $payment->provider,
            'Transaction' => $payment->provider_payment_id,
            'Amount' => number_format((float) $payment->amount, 2) . ' ' . $payment->currency,
            'Order' => $payment->order->reference_number,
            'Customer' => $payment->order->customer->full_name,
            'Failure reason' => $payment->failure_reason,
        ]">
            @can('orders.view')
                <div class="border-t border-neutral-200 px-5 py-3 dark:border-white/10">
                    <a href="{{ route('orders.show', $payment->order) }}" class="text-sm font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400">Open the order →</a>
                </div>
            @endcan
        </x-details>
    </div>
@endsection
