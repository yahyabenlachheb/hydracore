<?php

namespace App\Http\Requests;

use App\Support\Countries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('customer') ? 'customers.update' : 'customers.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->input('email'))),
            'country' => strtoupper(trim((string) $this->input('country'))) ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('customers', 'email')->ignore($this->route('customer'))],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', Rule::in(Countries::CODES)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['email.unique' => 'This email already belongs to a customer, possibly archived or on another brand.'];
    }
}
