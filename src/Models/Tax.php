<?php

namespace Dev3bdulrahman\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_taxes';

    protected $fillable = [
        'company_id', 'name', 'code', 'type', 'rate', 'scope', 'is_active', 'account_id',
    ];

    protected $casts = [
        'rate'      => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'tax_id');
    }
}
