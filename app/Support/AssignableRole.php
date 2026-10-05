<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Validation rule for "which roles can be given to a staff member".
 *
 * Any real web-guard role is allowed EXCEPT the superadmin role, so no form can
 * promote a normal user to superadmin. The superadmin account comes from .env only.
 */
final class AssignableRole
{
    public static function rule(): Exists
    {
        return Rule::exists('roles', 'id')->where(
            fn ($query) => $query->where('guard_name', 'web')->whereRaw('LOWER(name) <> ?', ['superadmin'])
        );
    }
}
