<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $this->user()?->isSuperAdmin()
            && (!$role || Str::lower($role->name) !== 'superadmin');
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                // Letters and digits in any language (Arabic included), spaces, _ and -.
                'regex:/^[\p{L}\p{N} _-]+$/u',
                'not_in:admin,superadmin,Admin,Superadmin',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.view' => ['sometimes', 'boolean'],
            'permissions.*.manage' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (in_array(Str::lower(trim((string) $this->input('name'))), ['admin', 'superadmin'], true)) {
                $validator->errors()->add('name', 'This role name is reserved.');
            }

            foreach (array_keys((array) $this->input('permissions', [])) as $page) {
                if (!array_key_exists($page, config('access.pages', []))) {
                    $validator->errors()->add('permissions', 'An unknown page permission was submitted.');
                }
            }
        });
    }
}
