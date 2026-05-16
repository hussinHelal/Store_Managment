<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
        return $user->isSuperAdmin();
    }

    public function view(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function store(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function destroy(User $user)
    {
        return $user->isSuperAdmin();
    }
}
