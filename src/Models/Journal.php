<?php

namespace Dev3bdulrahman\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_journals';

    protected $fillable = [
        'company_id', 'name', 'code', 'type', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'journal_id');
    }
}
