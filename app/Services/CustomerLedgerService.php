<?php

namespace App\Services;

use App\Models\customers;
use App\Models\installments;
use Illuminate\Support\Facades\Schema;

class CustomerLedgerService
{
    private const WALK_IN = [
        'زبون نقدي',
        'عميل نقدي',
        'unknown',
        'Unknown',
    ];

    public function syncNamedCustomer(string $name, ?installments $installment = null): ?customers
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        if ($name === '' || in_array($name, self::WALK_IN, true)) {
            return null;
        }

        $customer = customers::query()
            ->where('name', $name)
            ->lockForUpdate()
            ->first();

        if (! $customer) {
            $customer = customers::create([
                'name' => $name,
                'phone' => 'غير محدد',
                'address' => 'غير محدد',
            ]);
        }

        if ($installment && Schema::hasColumn('installments', 'customer_id')) {
            $installment->customer_id = $customer->id;
            $installment->save();
        }

        return $customer;
    }
}
