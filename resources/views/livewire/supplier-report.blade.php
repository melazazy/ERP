<div class="p-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">{{ __('messages.supplier_report') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Supplier Selection -->
                <div>
                    <label for="supplier" class="block text-sm font-medium text-gray-700">{{ __('messages.supplier') }}</label>
                    <select 
                        id="supplier" 
                        wire:model.live="selectedSupplier" 
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                        <option value="">{{ __('messages.select_supplier') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label for="dateFrom" class="block text-sm font-medium text-gray-700">{{ __('messages.date_from') }}</label>
                    <input 
                        type="date" 
                        id="dateFrom" 
                        wire:model.live="dateFrom" 
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                </div>

                <div>
                    <label for="dateTo" class="block text-sm font-medium text-gray-700">{{ __('messages.date_to') }}</label>
                    <input 
                        type="date" 
                        id="dateTo" 
                        wire:model.live="dateTo" 
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                </div>


                <!-- Document Number Search -->
                <div class="md:col-span-2">
                    <label for="docNumber" class="block text-sm font-medium text-gray-700">{{ __('messages.document_number') }}</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input 
                            type="text" 
                            id="docNumber" 
                            wire:model.live.debounce.300ms="docNumber"
                            class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-md sm:text-sm border-gray-300"
                            placeholder="{{ __('messages.search_by_doc_number') }}"
                        >
                    </div>
                </div>
            </div>

            <!-- Summary Card and Export Button -->
            @if($selectedSupplier)
                <div class="flex justify-between items-center mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg flex-1">
                        <h3 class="text-sm font-medium text-blue-800">{{ __('messages.total_receivings') }}</h3>
                        <p class="text-2xl font-semibold text-blue-600">{{ number_format($totalAmount, 2) }} {{ __('messages.currency') }}</p>
                    </div>
                    <div class="ml-4">
                        <button 
                            wire:click="exportToExcel"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring focus:ring-green-300 disabled:opacity-25 transition"
                            @if(count($receivings) === 0) disabled @endif
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('Export to Excel') }}
                        </button>
                    </div>
                </div>
            @endif

            <!-- Receivings Table -->
            @if($selectedSupplier && count($receivings) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.receipt_number') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.item') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.quantity') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.unit') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.unit_price') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.total') }}</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($receivings as $receiving)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receiving->receiving_number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receiving->item->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receiving->department->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($receiving->quantity, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receiving->unit->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($receiving->unit_price ?? $receiving->price ?? 0, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($receiving->total ?? ($receiving->quantity * ($receiving->unit_price ?? $receiving->price ?? 0)), 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $receiving->received_at ? \Carbon\Carbon::parse($receiving->received_at)->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(!$selectedSupplier)
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.select_supplier_to_view_report') }}</h3>
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.no_data_available') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('messages.no_data_found_for_selected_criteria') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
