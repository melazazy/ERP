<div class="py-12 text-left" dir="ltr">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <!-- Title -->
            <h2 class="text-2xl font-semibold mb-6">Item Card</h2>
            
            <!-- Header with Item Info -->
            <div class="mb-4 flex justify-between items-center">
                <div class="flex-1">
                    <input type="text" 
                        wire:model.live="itemSearch" 
                        wire:keydown.escape="$set('items', [])"
                        placeholder="Search for item..."
                        class="w-full p-2 border rounded-lg text-left">
                    @if($items && count($items))
                        <div class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg">
                            @foreach($items as $item)
                                <div class="p-2 hover:bg-gray-100 cursor-pointer"
                                    wire:click="selectItem({{ $item['id'] }})">
                                    {{ $item['name'] }}
                                    <span class="text-gray-500 text-sm">({{ $item['code'] }})</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if($selectedItem)
                    <div class="flex-1 text-center">
                        <div class="text-lg font-bold">{{ $itemSearch }}</div>
                        <div class="text-sm text-gray-600">Item Code: {{ $selectedItemCode }}</div>
                    </div>
                @endif
            </div>

            @if($selectedItem && !empty($movements))
                <div class="mb-4 flex justify-end">
                    <button wire:click="exportToExcel" 
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </button>
                </div>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-white border">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-white border">Document No.</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-white border">Department</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-white border">In</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-white border">Out</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider bg-white border">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($movements as $index => $movement)
                                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100">
                                    <td class="px-6 py-3 text-center text-sm text-gray-900 border">{{ date('d/m/Y', strtotime($movement['date'])) }}</td>
                                    <td class="px-6 py-3 text-center text-sm text-gray-900 border">{{ $movement['document_number'] }}</td>
                                    <td class="px-6 py-3 text-center text-sm text-gray-900 border">{{ $movement['description'] }}</td>
                                    <td class="px-6 py-3 text-center text-sm border {{ $movement['in_quantity'] ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                        {{ $movement['in_quantity'] ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-center text-sm text-gray-900 border">{{ $movement['out_quantity'] ?? '-' }}</td>
                                    <td class="px-6 py-3 text-center text-sm text-gray-900 border">{{ $movement['balance'] }}</td>
                                </tr>
                            @endforeach
                            <!-- Totals Row -->
                            <tr class="bg-white font-semibold">
                                <td colspan="3" class="px-6 py-3 text-center text-sm text-gray-700 border">Total</td>
                                <td class="px-6 py-3 text-center text-sm text-red-600 border">{{ $totalIn }}</td>
                                <td class="px-6 py-3 text-center text-sm text-gray-700 border">{{ $totalOut }}</td>
                                <td class="px-6 py-3 text-center text-sm text-gray-700 border">{{ $balance }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>