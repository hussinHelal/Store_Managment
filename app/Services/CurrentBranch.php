<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CurrentBranch
{
    public function id(?User $user = null): int
    {
        $branchId = $user?->branch_id;
        if ($branchId && DB::table('branches')->where('id', $branchId)->where('is_active', true)->exists()) {
            return (int) $branchId;
        }

        $mainBranchId = DB::table('branches')->where('is_main', true)->value('id');
        if (! $mainBranchId) {
            throw new RuntimeException('The main branch is not configured.');
        }

        return (int) $mainBranchId;
    }
}
