<?php

namespace Dev3bdulrahman\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_bank_accounts';

    protected $fillable = [
        'company_id', 'bank_name', 'account_name', 'account_number',
        'iban', 'swift_code', 'currency', 'opening_balance',
        'current_balance', 'is_active', 'account_id',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }
}
