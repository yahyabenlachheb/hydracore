<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('plan') ? 'plans.update' : 'plans.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'currency' => strtoupper(trim((string) $this->input('currency'))),
            'sort_order' => $this->integer('sort_order'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'duration_months' => ['required', 'integer', 'between:1,120'],
            'price' => ['required', 'decimal:0,2', 'min:0', 'max:99999999.99'],
            'currency' => ['required', 'string', 'size:3', 'alpha:ascii'],
            'number_of_connections' => ['required', 'integer', 'between:1,255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'between:-32768,32767'],
        ];
    }
}
