<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierDeletionService
{
    public function delete(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier): void {
            $locked = Supplier::query()
                ->lockForUpdate()
                ->findOrFail($supplier->getKey());

            if ($locked->purchases()->exists() || $locked->payments()->exists()) {
                throw ValidationException::withMessages([
                    'supplier' => 'لا يمكن حذف المورد لوجود مشتريات أو مدفوعات مسجلة.',
                ]);
            }

            $locked->delete();
        }, 3);
    }
}
