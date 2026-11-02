<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Support\BrandContext;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TrialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('trial') ? 'trials.update' : 'trials.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'packs' => array_values(array_filter((array) $this->input('packs', []))),
            'expires_at' => $this->input('expires_at') ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $brandId = app(BrandContext::class)->current()?->id;

        return [
            'customer_id' => [Rule::requiredIf(! $this->route('trial')), 'integer', function (string $attribute, mixed $value, Closure $fail) use ($brandId) {
                if (! Customer::visibleTo($brandId)->whereKey($value)->exists()) {
                    $fail('Choose a customer of this brand.');
                }
            }],
            'device_type' => ['required', 'string', 'max:50'],
            'device_info' => ['nullable', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
            'packs' => ['array'],
            'packs.*' => ['integer', 'exists:packs,id'],
        ];
    }
}
