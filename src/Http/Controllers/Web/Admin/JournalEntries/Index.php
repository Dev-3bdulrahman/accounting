<?php

namespace Dev3bdulrahman\Accounting\Http\Controllers\Web\Admin\JournalEntries;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class Index extends Component
{
    #[Layout('layouts.admin')]
    #[Title('Accounting')]
    public function render()
    {
        return view('accounting::livewire.admin.journal-entries.index');
    }
}
