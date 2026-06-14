<?php

namespace Dev3bdulrahman\Accounting\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'journal_entry_id' => $this->journal_entry_id,
            'account_id' => $this->account_id,
            'account_name' => $this->account?->name,
            'account_code' => $this->account?->code,
            'debit' => $this->debit,
            'credit' => $this->credit,
            'description' => $this->description,
        ];
    }
}
