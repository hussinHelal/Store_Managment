<?php

namespace App\Http\Requests;

use App\Support\AssignableRole;
use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $this->user()?->isSuperAdmin()
            && $user
            && !$user->system_account
            && !$user->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'role_id' => [
                'required',
                'integer',
                AssignableRole::rule(),
            ],
        ];
    }
}
