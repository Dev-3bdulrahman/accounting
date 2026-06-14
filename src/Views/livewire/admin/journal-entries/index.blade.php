<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ __('accounting::accounting.journal_entries') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('accounting::accounting.title') }}</p>
        </div>
        <button wire:click="openCreateModal()" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/20 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>{{ __('accounting::accounting.add') }}</span>
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative flex items-center">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('accounting::accounting.search_placeholder') }}"
                class="w-full ps-10 pe-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            <i data-lucide="search" class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
        </div>
        <select wire:model.live="filterStatus" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            <option value="">-- {{ __('accounting::accounting.status') }} --</option>
            <option value="draft">{{ __('accounting::accounting.status_draft') }}</option>
            <option value="posted">{{ __('accounting::accounting.status_posted') }}</option>
            <option value="cancelled">{{ __('accounting::accounting.status_cancelled') }}</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.entry_number') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.entry_date') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.description') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-end">{{ __('accounting::accounting.debit') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-end">{{ __('accounting::accounting.credit') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.status') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">{{ __('accounting::accounting.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($entries as $entry)
                        @php
                            $statusColors = [
                                'draft'     => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
                                'posted'    => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                            ];
                            $totalDebit  = $entry->lines->sum('debit');
                            $totalCredit = $entry->lines->sum('credit');
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-blue-600 dark:text-blue-400">{{ $entry->entry_number }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $entry->entry_date?->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate">{{ $entry->description }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white text-end">{{ number_format($totalDebit, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white text-end">{{ number_format($totalCredit, 2) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $statusColors[$entry->status] ?? '' }}">
                                    {{ __('accounting::accounting.status_' . $entry->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="viewEntry({{ $entry->id }})" title="{{ __('View') }}" class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                    @if($entry->status === 'draft')
                                        <button wire:click="postEntry({{ $entry->id }})" title="{{ __('accounting::accounting.post_entry') }}" class="px-3 py-1.5 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-bold text-xs rounded-lg transition-all">
                                            {{ __('accounting::accounting.post_entry') }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <i data-lucide="book-open" class="w-10 h-10 mx-auto mb-3 text-gray-300 dark:text-gray-600"></i>
                                <p>{{ __('accounting::accounting.no_records') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">{{ $entries->links() }}</div>
        @endif
    </div>

    {{-- Create Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-3xl w-full border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ __('accounting::accounting.add') }} {{ __('accounting::accounting.journal_entry') }}</h3>
                <button wire:click="closeCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form wire:submit.prevent="saveEntry" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.entry_date') }} *</label>
                        <input type="date" wire:model="newEntryDate" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('newEntryDate')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.description') }} *</label>
                        <input type="text" wire:model="newEntryDesc" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('newEntryDesc')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Lines --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">{{ __('accounting::accounting.account_name') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase">{{ __('accounting::accounting.description') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">{{ __('accounting::accounting.debit') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase">{{ __('accounting::accounting.credit') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($newLines as $i => $line)
                            <tr>
                                <td class="px-4 py-2">
                                    <select wire:model="newLines.{{ $i }}.account_id" class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">-- Account --</option>
                                        @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" wire:model="newLines.{{ $i }}.description" placeholder="{{ __('accounting::accounting.description') }}" class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" wire:model="newLines.{{ $i }}.debit" class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-end">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" step="0.01" wire:model="newLines.{{ $i }}.credit" class="w-full px-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-end">
                                </td>
                                <td class="px-4 py-2">
                                    @if(count($newLines) > 2)
                                    <button type="button" wire:click="removeLine({{ $i }})" class="text-red-500 hover:text-red-700 p-1 rounded transition-all">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                            <tr>
                                <td colspan="2" class="px-4 py-3">
                                    <button type="button" wire:click="addLine()" class="text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> {{ __('Add Line') }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right font-black text-gray-900 dark:text-white text-sm">
                                    {{ number_format(collect($newLines)->sum('debit'), 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-black text-gray-900 dark:text-white text-sm">
                                    {{ number_format(collect($newLines)->sum('credit'), 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if(collect($newLines)->sum('debit') == collect($newLines)->sum('credit') && collect($newLines)->sum('debit') > 0)
                                        <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                                    @elseif(collect($newLines)->sum('debit') > 0)
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @error('lines') <p class="text-sm text-red-600 dark:text-red-400 font-bold">⚠ {{ $message }}</p> @enderror

                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                    <button type="button" wire:click="closeCreateModal()" class="px-5 py-2 text-sm font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl transition-all">{{ __('accounting::accounting.cancel') }}</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg transition-all">{{ __('accounting::accounting.save') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
