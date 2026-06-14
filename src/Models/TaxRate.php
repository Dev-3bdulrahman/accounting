<?php

namespace Dev3bdulrahman\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    protected $table = 'accounting_tax_rates';

    protected $fillable = [
        'tax_id', 'rate', 'effective_from', 'effective_to',
    ];

    protected $casts = [
        'rate'           => 'decimal:4',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}
