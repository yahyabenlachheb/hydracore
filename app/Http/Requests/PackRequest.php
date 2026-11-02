<?php

namespace App\Http\Requests;

use App\Models\Pack;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->route('pack') ? 'packs.update' : 'packs.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'code' => strtoupper(trim((string) $this->input('code'))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(Pack::TYPES))],
            'code' => ['required', 'string', 'max:100', Rule::unique('packs')->where('type', $this->input('type'))->ignore($this->route('pack'))],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['code.unique' => 'A pack of this type already uses this code.'];
    }
}
