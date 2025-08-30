<div class="py-12 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <!-- Title -->
            <h2 class="text-2xl font-semibold mb-6">{{ __('messages.item_monitor') }}</h2>
            
            <!-- Header with Item Info -->
            <div class="mb-6 flex flex-col {{ app()->getLocale() === 'ar' ? 'items-end' : 'items-start' }} space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0">
                <div class="w-full md:w-1/2">
                    <div class="relative">
                        <input type="text" 
                            wire:model.live="itemSearch" 
                            wire:keydown.escape="$set('items', [])"
                            placeholder="{{ __('messages.search_item') }}..."
                            class="w-full p-2 border rounded-lg {{ app()->getLocale() === 'ar' ? 'pr-10 text-right' : 'pl-10 text-left' }}">
                        <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    @if(!empty($items))
                        <div class="absolute z-50 mt-1 w-full md:w-1/2 bg-white rounded-lg shadow-lg border border-gray-200">
                            @foreach($items as $item)
                                <div class="p-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-0"
                                    wire:click="selectItem({{ $item['id'] }})">
                                    <div class="font-medium">{{ $item['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $item['code'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if($selectedItem)
                    <div class="w-full md:w-1/2 mt-4 md:mt-0 {{ app()->getLocale() === 'ar' ? 'md:text-left' : 'md:text-right' }}">
                        <div class="text-lg font-bold">{{ $itemSearch }}</div>
                        <div class="text-sm text-gray-600">{{ __('messages.item_code') }}: {{ $selectedItemCode }}</div>
                    </div>
                @endif
            </div>

            @if($selectedItem && !empty($movements))
                <div class="mb-4 flex {{ app()->getLocale() === 'ar' ? 'justify-start' : 'justify-end' }}">
                    <button wire:click="exportToExcel" 
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center">
                        <svg class="w-4 h-4 {{ app()->getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ __('messages.export') }}
                    </button>
                </div>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-gray-50">
                            <tr>
                                <th rowspan="2" class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.date') }}</th>
                                <th rowspan="2" class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.document_no') }}</th>
                                <th rowspan="2" class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.description') }}</th>
                                <th colspan="3" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider border">{{ __('messages.quantity') }}</th>
                                <th colspan="3" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider border">{{ __('messages.price') }}</th>
                            </tr>
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border text-center">{{ __('messages.in') }}</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border text-center">{{ __('messages.out') }}</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border text-center">{{ __('messages.balance') }}</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border text-center">{{ __('messages.in') }}</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border text-center">{{ __('messages.out') }}</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider border text-center">{{ __('messages.balance') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($movements as $index => $movement)
                                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100">
                                    <td class="px-6 py-3 text-sm text-gray-900 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ date('d/m/Y', strtotime($movement['date'])) }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                        <a href="{{ route('transaction.details', ['type' => $movement['transaction_type'], 'number' => $movement['document_number']]) }}" 
                                           class="text-blue-600 hover:text-blue-800 underline cursor-pointer">
                                            {{ $movement['document_number'] }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-900 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $movement['description'] }}</td>
                                    <td class="px-6 py-3 text-sm border text-center {{ $movement['in'] ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                        {{ $movement['in'] ? number_format($movement['in'], 2) : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-900 border text-center">{{ $movement['out'] ? number_format($movement['out'], 2) : '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 border text-center">{{ number_format($movement['balance'], 2) }}</td>
                                    <td class="px-6 py-3 text-sm border text-center {{ $movement['in_price'] ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                        {{ $movement['in_price'] ? number_format($movement['in_price'], 2) : '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-900 border text-center">{{ $movement['out_price'] ? number_format($movement['out_price'], 2) : '-' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-900 border text-center">{{ number_format($movement['balance_price'], 2) }}</td>
                                </tr>
                            @endforeach
                            <!-- Totals Row -->
                            <tr class="bg-gray-100 font-semibold">
                                <td colspan="3" class="px-6 py-3 text-sm text-gray-900 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.total') }}</td>
                                <td class="px-6 py-3 text-sm text-red-600 border text-center">{{ number_format($totalIn, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700 border text-center">{{ number_format($totalOut, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700 border text-center">{{ number_format($balance, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-red-600 border text-center">{{ number_format($totalInPrice, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700 border text-center">{{ number_format($totalOutPrice, 2) }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700 border text-center">{{ number_format($balancePrice, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>