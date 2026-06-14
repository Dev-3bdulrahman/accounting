<?php

namespace Dev3bdulrahman\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class JournalEntry extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'accounting_journal_entries';

    protected $fillable = [
        'company_id',
        'branch_id',
        'entry_number',
        'entry_date',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }
}
