<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Trial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('orders.pay');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provider_payment_id' => trim((string) $this->input('provider_payment_id')) ?: null,
            'packs' => array_values(array_filter((array) $this->input('packs', []))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Order $order */
        $order = $this->route('order');
        // A new order's line needs device details the order doesn't store.
        $needsLine = fn () => $order->type === 'new' && $this->input('outcome') === 'succeeded';

        return [
            'outcome' => ['required', Rule::in(['succeeded', 'failed'])],
            'provider' => ['required', Rule::in(array_keys(Payment::PROVIDERS))],
            'provider_payment_id' => ['nullable', 'string', 'max:150', Rule::unique('payments')->where('provider', $this->input('provider'))],
            'failure_reason' => ['nullable', 'string', 'max:255'],
            'device_type' => [Rule::requiredIf($needsLine), 'nullable', 'string', 'max:50'],
            'device_info' => ['nullable', 'string', 'max:255'],
            'packs' => ['array'],
            'packs.*' => ['integer', 'exists:packs,id'],
            'trial_id' => ['nullable', 'integer', Rule::exists(Trial::class, 'id')->where('customer_id', $order->customer_id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['provider_payment_id.unique' => 'This transaction was already recorded for this provider.'];
    }
}
