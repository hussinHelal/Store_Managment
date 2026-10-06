<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfilePolicy
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
        return $user->can('page.profile.view') || $user->can('page.profile.manage');
    }

    public function view(User $user)
    {
        return $user->can('page.profile.view') || $user->can('page.profile.manage');
    }

    public function store(User $user)
    {
        return $user->can('page.profile.manage');
    }

    public function update(User $user)
    {
        return $user->can('page.profile.manage');
    }

    public function destroy(User $user)
    {
        return false;
    }
}
