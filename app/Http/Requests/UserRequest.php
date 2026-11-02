<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows($this->target() ? 'users.update' : 'users.create');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => strtolower(trim((string) $this->input('username'))),
            'is_active' => $this->boolean('is_active'),
            'is_super_admin' => $this->boolean('is_super_admin'),
            'roles' => array_values(array_filter((array) $this->input('roles', []))),
            'brands' => array_values(array_filter((array) $this->input('brands', []))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $target = $this->target();

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash:ascii', Rule::unique('users', 'username')->ignore($target)],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')->ignore($target)],
            'password' => [$target ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'is_active' => ['boolean'],
            'is_super_admin' => ['boolean'],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'brands' => ['array'],
            'brands.*' => ['integer', 'exists:brands,id'],
        ];
    }

    private function target(): ?User
    {
        return $this->route('user');
    }
}
