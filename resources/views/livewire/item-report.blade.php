<div class="py-12 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <!-- Title -->
            <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">{{ __('messages.item_report') }}</h1>

            <!-- Controls Section -->
            <div class="mb-6 flex flex-col {{ app()->getLocale() === 'ar' ? 'items-end' : 'items-start' }} space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0 md:space-x-4">
                <div class="w-full md:flex-1" x-data="{ open: false, selected: '{{ $selectedDepartment }}' }" x-init="$watch('selected', value => $wire.set('selectedDepartment', value, true))">
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.select_department') }}</label>
                    <div class="relative">
                        <button @click="open = !open" type="button" 
                                class="w-full bg-white border border-gray-300 rounded-md shadow-sm px-4 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm flex justify-between items-center"
                                :class="{ 'text-right': '{{ app()->getLocale() }}' === 'ar' }">
                            <span x-text="selected === '' ? '{{ __('messages.select_department') }}' : 
                                         selected === 'all' ? '{{ __('messages.all_departments') }}' : 
                                         {{ json_encode(collect($departments)->pluck('name', 'id')->toArray()) }}[selected] || '{{ __('messages.select_department') }}'"></span>
                            <svg class="h-5 w-5 text-gray-400" :class="{ 'transform rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                             style="display: none;">
                            <div class="cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                                 :class="{ 'text-right': '{{ app()->getLocale() }}' === 'ar' }"
                                 @click="selected = 'all'; open = false">
                                <span class="block truncate">{{ __('messages.all_departments') }}</span>
                            </div>
                            @foreach($departments as $department)
                                <div class="cursor-default select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                                     :class="{ 'text-right': '{{ app()->getLocale() }}' === 'ar' }"
                                     @click="selected = '{{ $department['id'] }}'; open = false">
                                    <span class="block truncate">{{ $department['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" name="department" x-model="selected">
                    </div>
                </div>
                
                @if($selectedDepartment)
                <div class="w-full md:flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search_items') }}</label>
                    <div class="relative">
                        <input wire:model.live="search" type="text" id="search" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 {{ app()->getLocale() === 'ar' ? 'pr-10 text-right' : 'pl-10 text-left' }}" 
                               placeholder="{{ __('messages.search_item_placeholder') }}...">
                        <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'right-0 pr-3' : 'left-0 pl-3' }} flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.export') }}</label>
                    <button wire:click="exportToExcel" 
                            class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="h-5 w-5 {{ app()->getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('messages.export_excel') }}
                    </button>
                </div>
                @endif
            </div>

            @if($selectedDepartment && $items)
            <!-- Report Table -->
            <div class="overflow-x-auto shadow-xl rounded-lg">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <!-- Basic Info -->
                            <th rowspan="2" class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <span>{{ __('messages.item') }}</span>
                                </div>
                            </th>
                            <th rowspan="2" class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    <span>{{ __('messages.code') }}</span>
                                </div>
                            </th>
                            <th rowspan="2" class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <div class="flex items-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                    </svg>
                                    <span>{{ __('messages.unit') }}</span>
                                </div>
                            </th>
                            
                            <!-- Opening Balance -->
                            <th colspan="3" class="px-6 py-4 text-center text-sm font-semibold text-blue-800 uppercase tracking-wider border-b-2 border-blue-200 bg-blue-50 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                    <span>{{ __('messages.opening_balance') }}</span>
                                </div>
                            </th>
                            
                            <!-- IN -->
                            <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-green-800 uppercase tracking-wider border-b-2 border-green-200 bg-green-50 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>{{ __('messages.in') }}</span>
                                </div>
                            </th>
                            
                            <!-- Total Available -->
                            <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-indigo-800 uppercase tracking-wider border-b-2 border-indigo-200 bg-indigo-50 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <span>{{ __('messages.total_available') }}</span>
                                </div>
                            </th>
                            
                            <!-- OUT -->
                            <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-red-800 uppercase tracking-wider border-b-2 border-red-200 bg-red-50 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                    <span>{{ __('messages.out') }}</span>
                                </div>
                            </th>
                            
                            <!-- Balance -->
                            <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-yellow-800 uppercase tracking-wider border-b-2 border-yellow-200 bg-yellow-50 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                    </svg>
                                    <span>{{ __('messages.balance') }}</span>
                                </div>
                            </th>
                            
                            <!-- Additional -->
                            <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-purple-800 uppercase tracking-wider border-b-2 border-purple-200 bg-purple-50 transition duration-150 ease-in-out">
                                <div class="flex items-center justify-center space-x-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <span>{{ __('messages.additional') }}</span>
                                </div>
                            </th>
                        </tr>
                        <tr class="bg-gray-50">
                            <!-- Opening Balance Details -->
                            <th class="px-6 py-3 text-xs font-medium text-blue-700 uppercase tracking-wider border-b border-blue-200 bg-blue-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.quantity') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-blue-700 uppercase tracking-wider border-b border-blue-200 bg-blue-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.unit_cost') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-blue-700 uppercase tracking-wider border-b border-blue-200 bg-blue-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.amount') }}</th>
                            
                            <!-- IN Details -->
                            <th class="px-6 py-3 text-xs font-medium text-green-700 uppercase tracking-wider border-b border-green-200 bg-green-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.quantity') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-green-700 uppercase tracking-wider border-b border-green-200 bg-green-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.amount') }}</th>
                            
                            <!-- Total Available Details -->
                            <th class="px-6 py-3 text-xs font-medium text-indigo-700 uppercase tracking-wider border-b border-indigo-200 bg-indigo-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.quantity') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-indigo-700 uppercase tracking-wider border-b border-indigo-200 bg-indigo-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.amount') }}</th>
                            
                            <!-- OUT Details -->
                            <th class="px-6 py-3 text-xs font-medium text-red-700 uppercase tracking-wider border-b border-red-200 bg-red-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.quantity') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-red-700 uppercase tracking-wider border-b border-red-200 bg-red-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.amount') }}</th>
                            
                            <!-- Balance Details -->
                            <th class="px-6 py-3 text-xs font-medium text-yellow-700 uppercase tracking-wider border-b border-yellow-200 bg-yellow-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.quantity') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-yellow-700 uppercase tracking-wider border-b border-yellow-200 bg-yellow-50 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ __('messages.amount') }}</th>
                            
                            <!-- Additional Details -->
                            <th class="px-6 py-3 text-xs font-medium text-purple-700 uppercase tracking-wider border-b border-purple-200 bg-purple-50 text-center">{{ __('messages.notes') }}</th>
                            <th class="px-6 py-3 text-xs font-medium text-purple-700 uppercase tracking-wider border-b border-purple-200 bg-purple-50 text-center">{{ __('messages.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($items as $item)
                            <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $item['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $item['code'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $item['unit'] }}</td>
                                
                                <!-- Opening Balance -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-blue-50">{{ number_format($item['opening_quantity'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-blue-50">{{ number_format($item['opening_unit_cost'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-blue-50">{{ number_format($item['opening_amount'], 2) }}</td>
                                
                                <!-- IN -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-green-50">{{ number_format($item['in_quantity'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-green-50">{{ number_format($item['in_amount'], 2) }}</td>
                                
                                <!-- Total Available -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-indigo-50">{{ number_format($item['total_available_quantity'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-indigo-50">{{ number_format($item['total_available_amount'], 2) }}</td>
                                
                                <!-- OUT -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-red-50">{{ number_format($item['out_quantity'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-red-50">{{ number_format($item['out_amount'], 2) }}</td>
                                
                                <!-- Balance -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-yellow-50">{{ number_format($item['balance_quantity'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-yellow-50">{{ number_format($item['balance_amount'], 2) }}</td>
                                
                                <!-- Additional -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center bg-purple-50">{{ $item['act'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center bg-purple-50">{{ $item['diff'] }}</td>
                            </tr>
                        @endforeach
                        <tr class="bg-gray-50 font-bold">
                            <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ __('messages.totals') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-blue-50">{{ number_format($totalOpeningQuantity, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-blue-50">-</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-blue-50">{{ number_format($totalOpeningAmount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-green-50">{{ number_format($totalInQuantity, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-green-50">{{ number_format($totalInAmount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-indigo-50">{{ number_format($totalAvailableQuantity, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-indigo-50">{{ number_format($totalAvailableAmount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-red-50">{{ number_format($totalOutQuantity, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-red-50">{{ number_format($totalOutAmount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-yellow-50">{{ number_format($totalBalanceQuantity, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }} bg-yellow-50">{{ number_format($totalBalanceAmount, 2) }}</td>
                            <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center bg-purple-50"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @else
            <!-- No Selection Message -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.no_department_selected') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('messages.select_department_to_view_items') }}</p>
            </div>
            @endif
        </div>
    </div>
</div>