<?php

namespace App\Http\Requests;

use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->permission() ? 'permissions.update' : 'permissions.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'slug' => strtolower(trim((string) $this->input('slug'))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $permission = $this->permission();

        return [
            'name' => ['required', 'string', 'max:100'],
            // System slugs are locked; the controller ignores the field for them.
            'slug' => $permission?->isSystem() ? ['nullable'] : [
                'required', 'string', 'max:150',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*(?:\.[a-z0-9]+(?:-[a-z0-9]+)*)+$/',
                Rule::unique('permissions', 'slug')->ignore($permission),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'Use lowercase words separated by dots, e.g. “offers.view” or “sending-jobs.create”.',
        ];
    }

    private function permission(): ?Permission
    {
        return $this->route('permission');
    }
}
