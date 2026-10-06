<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstallmentsPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user)
    {
        return $user->can('page.installments.view') || $user->can('page.installments.manage');
    }

    public function view(User $user)
    {
        return $user->can('page.installments.view') || $user->can('page.installments.manage');
    }

    public function store(User $user)
    {
        return $user->can('page.installments.manage');
    }

    public function update(User $user)
    {
        return $user->can('page.installments.manage');
    }

    public function destroy(User $user)
    {
        return $user->can('page.installments.manage');
    }
}
