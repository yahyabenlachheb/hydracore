<?php

namespace App\Http\Requests;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->brand() ? 'brands.update' : 'brands.create');
    }

    protected function prepareForValidation(): void
    {
        $website = trim((string) $this->input('website'));

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            // Accept "example.com" and store it as a full URL.
            'website' => $website !== '' && ! preg_match('#^https?://#i', $website) ? "https://{$website}" : ($website ?: null),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('brands', 'name')->ignore($this->brand())],
            'website' => ['nullable', 'url:http,https', 'max:190'],
            'is_active' => ['boolean'],
        ];
    }

    private function brand(): ?Brand
    {
        return $this->route('brand');
    }
}
