<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomersPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user)
    {
        return true;
    }

    public function store(User $user)
    {
        return $user->isAdmin() || $user->isSuperAdmin() || $user->isCashier();
    }

    public function update(User $user)
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function destroy(User $user)
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
