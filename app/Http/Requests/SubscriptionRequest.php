<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\Subscription;
use App\Support\BrandContext;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->target() ? 'subscriptions.update' : 'subscriptions.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'packs' => array_values(array_filter((array) $this->input('packs', []))),
            'starts_at' => $this->input('starts_at') ?: null,
            'expires_at' => $this->input('expires_at') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $brandId = app(BrandContext::class)->current()?->id;
        $target = $this->target();

        return [
            // The owner of an existing line never changes; its history stays with them.
            'customer_id' => [Rule::requiredIf(! $target), 'integer', function (string $attribute, mixed $value, Closure $fail) use ($brandId) {
                if (! Customer::visibleTo($brandId)->whereKey($value)->exists()) {
                    $fail('Choose a customer of this brand.');
                }
            }],
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where('brand_id', $brandId)],
            'device_type' => ['required', 'string', 'max:50'],
            'device_info' => ['nullable', 'string', 'max:255'],
            'status' => [Rule::requiredIf(! $target), Rule::in(['pending', 'active'])],
            'starts_at' => ['nullable', 'date'],
            // A running line always has an end date (chk_subs_active).
            'expires_at' => [Rule::requiredIf(fn () => $target && in_array($target->status, ['active', 'suspended'], true)), 'nullable', 'date', 'after:starts_at'],
            'packs' => ['array'],
            'packs.*' => ['integer', 'exists:packs,id'],
        ];
    }

    private function target(): ?Subscription
    {
        return $this->route('subscription');
    }
}
