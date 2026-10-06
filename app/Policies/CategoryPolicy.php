<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
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
        return $user->can('page.categories.view') || $user->can('page.categories.manage');
    }

    public function view(User $user)
    {
        return $user->can('page.categories.view') || $user->can('page.categories.manage');
    }

    public function store(User $user)
    {
        return $user->can('page.categories.manage');
    }

    public function update(User $user)
    {
        return $user->can('page.categories.manage');
    }

    public function destroy(User $user)
    {
        return $user->can('page.categories.manage');
    }
}
