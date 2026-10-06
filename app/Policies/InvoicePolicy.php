<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
        return $user->can('page.invoices.view') || $user->can('page.invoices.manage');
    }

    public function view(User $user)
    {
        return $user->can('page.invoices.view') || $user->can('page.invoices.manage');
    }

    public function store(User $user)
    {
        return $user->can('page.invoices.manage');
    }

    public function update(User $user)
    {
        return $user->can('page.invoices.manage');
    }

    public function destroy(User $user)
    {
        return $user->can('page.invoices.manage');
    }

    public function refund(User $user)
    {
        return $user->can('page.invoices.manage');
    }
}
