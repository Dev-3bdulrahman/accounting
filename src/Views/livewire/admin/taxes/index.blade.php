<div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="space-y-1">
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ __('accounting::accounting.taxes') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('accounting::accounting.title') }}</p>
        </div>
        <button wire:click="openModal()" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/20 transition-all">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>{{ __('accounting::accounting.add') }}</span>
        </button>
    </div>
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
        <div class="relative flex items-center">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('accounting::accounting.search_placeholder') }}"
                class="w-full max-w-xs ps-10 pe-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            <i data-lucide="search" class="absolute start-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.tax_code') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.tax_name') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.tax_rate') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.tax_scope') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('accounting::accounting.status') }}</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">{{ __('accounting::accounting.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($taxes as $tax)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono font-bold text-blue-600 dark:text-blue-400">{{ $tax->code }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ $tax->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $tax->rate }}{{ $tax->type === 'percentage' ? '%' : ' SAR' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ __('accounting::accounting.scope_' . $tax->scope) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $tax->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                    {{ $tax->is_active ? __('accounting::accounting.active') : __('accounting::accounting.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openModal({{ $tax->id }})" class="p-1.5 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button wire:click="deleteTax({{ $tax->id }})" wire:confirm="{{ __('This action cannot be undone.') }}" class="p-1.5 text-red-500 hover:text-red-700 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('accounting::accounting.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($taxes->hasPages())<div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">{{ $taxes->links() }}</div>@endif
    </div>

    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-md w-full border border-gray-100 dark:border-gray-800 shadow-2xl overflow-hidden animate__animated animate__fadeInUp animate__faster">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $editId ? __('accounting::accounting.edit') : __('accounting::accounting.add') }} {{ __('accounting::accounting.tax') }}</h3>
                <button wire:click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form wire:submit.prevent="save" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.tax_name') }} *</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.tax_code') }} *</label>
                        <input type="text" wire:model="code" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('code')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.tax_rate') }} *</label>
                        <input type="number" step="0.01" wire:model="rate" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.tax_type') }}</label>
                        <select wire:model="type" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">{{ __('accounting::accounting.type_percentage') }}</option>
                            <option value="fixed">{{ __('accounting::accounting.type_fixed') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ __('accounting::accounting.tax_scope') }}</label>
                        <select wire:model="scope" class="w-full px-4 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="both">{{ __('accounting::accounting.scope_both') }}</option>
                            <option value="sale">{{ __('accounting::accounting.scope_sale') }}</option>
                            <option value="purchase">{{ __('accounting::accounting.scope_purchase') }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="checkbox" wire:model="is_active" id="tax_active" class="w-4 h-4 rounded accent-blue-600">
                    <label for="tax_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('accounting::accounting.active') }}</label>
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
