<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletExpense extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'spent_on' => 'date',
            'voided_at' => 'datetime',
        ];
    }
}
