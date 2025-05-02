<div class="container mx-auto mt-5 p-6">
    <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">Item Report</h1>

    <!-- Controls Section -->
    <div class="mb-6 flex items-center gap-4 bg-white p-4 rounded-lg shadow-sm">
        <div class="flex-1">
            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Select Department</label>
            <select wire:model.live="selectedDepartment" id="department" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="">Select Department</option>
                <option value="all">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                @endforeach
            </select>
        </div>
        
        @if($selectedDepartment)
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Items</label>
            <div class="relative">
                <input wire:model.live="search" type="text" id="search" 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pl-10" 
                       placeholder="Search by item name or code...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="flex-none">
            <label class="block text-sm font-medium text-gray-700 mb-1">Export</label>
            <button wire:click="exportToExcel" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export to Excel
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
                    <th rowspan="2" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span>Item</span>
                        </div>
                    </th>
                    <th rowspan="2" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>Code</span>
                        </div>
                    </th>
                    <th rowspan="2" class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider border-b-2 border-gray-200 bg-gray-50">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                            <span>Unit</span>
                        </div>
                    </th>
                    
                    <!-- Opening Balance -->
                    <th colspan="3" class="px-6 py-4 text-center text-sm font-semibold text-blue-800 uppercase tracking-wider border-b-2 border-blue-200 bg-blue-50 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <span>Opening Balance</span>
                        </div>
                    </th>
                    
                    <!-- IN -->
                    <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-green-800 uppercase tracking-wider border-b-2 border-green-200 bg-green-50 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>IN</span>
                        </div>
                    </th>
                    
                    <!-- Total Available -->
                    <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-indigo-800 uppercase tracking-wider border-b-2 border-indigo-200 bg-indigo-50 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span>Total Available</span>
                        </div>
                    </th>
                    
                    <!-- OUT -->
                    <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-red-800 uppercase tracking-wider border-b-2 border-red-200 bg-red-50 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                            </svg>
                            <span>OUT</span>
                        </div>
                    </th>
                    
                    <!-- Balance -->
                    <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-yellow-800 uppercase tracking-wider border-b-2 border-yellow-200 bg-yellow-50 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                            <span>Balance</span>
                        </div>
                    </th>
                    
                    <!-- Additional -->
                    <th colspan="2" class="px-6 py-4 text-center text-sm font-semibold text-purple-800 uppercase tracking-wider border-b-2 border-purple-200 bg-purple-50 transition duration-150 ease-in-out">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Additional</span>
                        </div>
                    </th>
                </tr>
                <tr class="bg-gray-50">
                    <!-- Opening Balance Details -->
                    <th class="px-6 py-3 text-right text-xs font-medium text-blue-700 uppercase tracking-wider bg-blue-50">Quantity</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-blue-700 uppercase tracking-wider bg-blue-50">Unit Cost</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-blue-700 uppercase tracking-wider bg-blue-50">Amount</th>
                    
                    <!-- IN Details -->
                    <th class="px-6 py-3 text-right text-xs font-medium text-green-700 uppercase tracking-wider bg-green-50">Quantity</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-green-700 uppercase tracking-wider bg-green-50">Amount</th>
                    
                    <!-- Total Available Details -->
                    <th class="px-6 py-3 text-right text-xs font-medium text-indigo-700 uppercase tracking-wider bg-indigo-50">Quantity</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-indigo-700 uppercase tracking-wider bg-indigo-50">Amount</th>
                    
                    <!-- OUT Details -->
                    <th class="px-6 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider bg-red-50">Quantity</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider bg-red-50">Amount</th>
                    
                    <!-- Balance Details -->
                    <th class="px-6 py-3 text-right text-xs font-medium text-yellow-700 uppercase tracking-wider bg-yellow-50">Quantity</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-yellow-700 uppercase tracking-wider bg-yellow-50">Amount</th>
                    
                    <!-- Additional Details -->
                    <th class="px-6 py-3 text-center text-xs font-medium text-purple-700 uppercase tracking-wider bg-purple-50">Notes</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-purple-700 uppercase tracking-wider bg-purple-50">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($items as $item)
                    <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['code'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['unit'] }}</td>
                        
                        <!-- Opening Balance -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-blue-50">{{ number_format($item['opening_quantity'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-blue-50">{{ number_format($item['opening_unit_cost'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-blue-50">{{ number_format($item['opening_amount'], 2) }}</td>
                        
                        <!-- IN -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-green-50">{{ number_format($item['in_quantity'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-green-50">{{ number_format($item['in_amount'], 2) }}</td>
                        
                        <!-- Total Available -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right bg-indigo-50">{{ number_format($item['total_available_quantity'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right bg-indigo-50">{{ number_format($item['total_available_amount'], 2) }}</td>
                        
                        <!-- OUT -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-red-50">{{ number_format($item['out_quantity'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-red-50">{{ number_format($item['out_amount'], 2) }}</td>
                        
                        <!-- Balance -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right bg-yellow-50">{{ number_format($item['balance_quantity'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right bg-yellow-50">{{ number_format($item['balance_amount'], 2) }}</td>
                        
                        <!-- Additional -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center bg-purple-50">{{ $item['act'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center bg-purple-50">{{ $item['diff'] }}</td>
                    </tr>
                @endforeach
                <tr class="bg-gray-50 font-bold">
                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Totals</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-blue-50">{{ number_format($totalOpeningQuantity, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-blue-50">-</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-blue-50">{{ number_format($totalOpeningAmount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-green-50">{{ number_format($totalInQuantity, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-green-50">{{ number_format($totalInAmount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-indigo-50">{{ number_format($totalAvailableQuantity, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-indigo-50">{{ number_format($totalAvailableAmount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-red-50">{{ number_format($totalOutQuantity, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-red-50">{{ number_format($totalOutAmount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-yellow-50">{{ number_format($totalBalanceQuantity, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right bg-yellow-50">{{ number_format($totalBalanceAmount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">-</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">-</td>
                </tr>
            </tbody>
        </table>
    </div>
    @else
    <!-- No Selection Message -->
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No Items to Display</h3>
        <p class="mt-1 text-sm text-gray-500">Please select a department to view items.</p>
    </div>
    @endif
</div>