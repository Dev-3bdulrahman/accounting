<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ __('accounting::accounting.accounts') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('accounting::accounting.title') }}</p>
        </div>
        <button wire:click="openModal()" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/20 transition-all">
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
        <select wire:model.live="filterType" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            <option value="">-- {{ __('accounting::accounting.account_type') }} --</option>
            @foreach(['asset','liability','equity','revenue','expense'] as $t)
                <option value="{{ $t }}">{{ __('accounting::accounting.types.' . $t) }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.account_code') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.account_name') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.account_type') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.status') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">{{ __('accounting::accounting.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-bold text-blue-600 dark:text-blue-400">{{ $account->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                {{ $account->name }}
                                @if($account->parent_id)
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-normal ms-2">{{ $account->parent?->name }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $typeColors = [
                                        'asset'     => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                                        'liability' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400',
                                        'equity'    => 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
                                        'revenue'   => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
                                        'expense'   => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $typeColors[$account->type] ?? '' }}">
                                    {{ __('accounting::accounting.types.' . $account->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $account->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                    {{ $account->is_active ? __('accounting::accounting.active') : __('accounting::accounting.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openModal({{ $account->id }})" title="{{ __('accounting::accounting.edit') }}" class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="confirmAccountDelete({{ $account->id }})" title="{{ __('accounting::accounting.delete') }}" class="p-1.5 text-red-500 hover:text-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 text-gray-300 dark:text-gray-600"></i>
                                <p>{{ __('accounting::accounting.no_records') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">{{ $accounts->links() }}</div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-lg w-full border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">
                        {{ $editId ? __('accounting::accounting.edit') : __('accounting::accounting.add') }} {{ __('accounting::accounting.account') }}
                    </h3>
                    <button wire:click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.account_code') }} *</label>
                            <input type="text" wire:model="code" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            @error('code') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.account_type') }} *</label>
                            <select wire:model="type" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                @foreach(['asset','liability','equity','revenue','expense'] as $t)
                                    <option value="{{ $t }}">{{ __('accounting::accounting.types.' . $t) }}</option>
                                @endforeach
                            </select>
                            @error('type') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.account_name') }} *</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.parent_account') }}</label>
                        <select wire:model="parent_id" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="">-- {{ __('accounting::accounting.parent_account') }} --</option>
                            @foreach($parentAccounts as $pa)
                                @if($pa->id !== $editId)
                                    <option value="{{ $pa->id }}">{{ $pa->code }} - {{ $pa->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="w-4 h-4 rounded accent-blue-600">
                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('accounting::accounting.active') }}</label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal()" class="px-5 py-2 text-sm font-bold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl transition-all">
                            {{ __('accounting::accounting.cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2 text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg transition-all">
                            {{ __('accounting::accounting.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    function confirmAccountDelete(id) {
        window.dispatchEvent(new CustomEvent('swal:confirm', {
            detail: {
                title: '{{ __('Delete Account') }}',
                text: '{{ __('This action cannot be undone.') }}',
                icon: 'warning',
                confirmButtonText: '{{ __('accounting::accounting.delete') }}',
                cancelButtonText: '{{ __('accounting::accounting.cancel') }}',
                onConfirm: 'deleteAccount',
                params: [id]
            }
        }));
    }
</script>
