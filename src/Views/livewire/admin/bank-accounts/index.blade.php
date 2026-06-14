<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ __('accounting::accounting.bank_accounts') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('accounting::accounting.title') }}</p>
        </div>
        <button wire:click="openModal()" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/20 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i><span>{{ __('accounting::accounting.add') }}</span>
        </button>
    </div>
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="relative flex items-center">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('accounting::accounting.search_placeholder') }}"
                class="w-full max-w-xs ps-10 pe-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            <i data-lucide="search" class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($bankAccounts as $ba)
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <i data-lucide="landmark" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                </div>
                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $ba->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                    {{ $ba->is_active ? __('accounting::accounting.active') : __('accounting::accounting.inactive') }}
                </span>
            </div>
            <h3 class="font-black text-gray-900 dark:text-white text-base">{{ $ba->account_name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $ba->bank_name }}</p>
            <p class="text-xs font-mono text-gray-400 dark:text-gray-500 mt-1">{{ $ba->account_number }}</p>
            @if($ba->iban)<p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">IBAN: {{ $ba->iban }}</p>@endif
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('accounting::accounting.current_balance') }}</p>
                    <p class="text-lg font-black text-gray-900 dark:text-white">{{ number_format($ba->current_balance, 2) }} <span class="text-sm font-normal text-gray-500">{{ $ba->currency }}</span></p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="openModal({{ $ba->id }})" class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <button wire:click="deleteBankAccount({{ $ba->id }})" wire:confirm="{{ __('This action cannot be undone.') }}" class="p-1.5 text-red-500 hover:text-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-16 text-center text-gray-500 dark:text-gray-400">
            <i data-lucide="landmark" class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600"></i>
            <p>{{ __('accounting::accounting.no_records') }}</p>
        </div>
        @endforelse
    </div>
    @if($bankAccounts->hasPages())<div class="mt-4">{{ $bankAccounts->links() }}</div>@endif

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $editId ? __('accounting::accounting.edit') : __('accounting::accounting.add') }} {{ __('accounting::accounting.bank_account') }}</h3>
                <button wire:click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form wire:submit.prevent="save" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.bank_name') }} *</label>
                        <input type="text" wire:model="bank_name" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('bank_name')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.account_name') }} *</label>
                        <input type="text" wire:model="account_name" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('account_name')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.account_number_label') }} *</label>
                        <input type="text" wire:model="account_number" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('account_number')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.currency') }}</label>
                        <select wire:model="currency" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option>SAR</option><option>EGP</option><option>USD</option><option>EUR</option><option>AED</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.iban') }}</label>
                        <input type="text" wire:model="iban" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.swift_code') }}</label>
                        <input type="text" wire:model="swift_code" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                @if(!$editId)
                <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.opening_balance') }}</label>
                    <input type="number" step="0.01" wire:model="opening_balance" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                @endif
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="is_active" id="ba_active" class="w-4 h-4 rounded accent-blue-600">
                    <label for="ba_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('accounting::accounting.active') }}</label>
                </div>
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                    <button type="button" wire:click="closeModal()" class="px-5 py-2 text-sm font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl transition-all">{{ __('accounting::accounting.cancel') }}</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg transition-all">{{ __('accounting::accounting.save') }}</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
