<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
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
        return $user->can('page.suppliers.view') || $user->can('page.suppliers.manage');
    }

    public function view(User $user)
    {
        return $user->can('page.suppliers.view') || $user->can('page.suppliers.manage');
    }

    public function store(User $user)
    {
        return $user->can('page.suppliers.manage');
    }

    public function update(User $user)
    {
        return $user->can('page.suppliers.manage');
    }

    public function destroy(User $user)
    {
        return $user->can('page.suppliers.manage');
    }
}
