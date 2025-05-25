<div class="container mx-auto p-4">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold mb-6">{{ __('messages.export_reports') }}</h2>
        
        <!-- Report Filters -->
        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.report_type') }}</label>
                    <select wire:model.live="reportType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="receivings">{{ __('messages.receivings') }}</option>
                        <option value="requisitions">{{ __('messages.requisitions') }}</option>
                        <option value="trusts">{{ __('messages.trusts') }}</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.start_date') }}</label>
                    <input type="date" wire:model.live="startDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.end_date') }}</label>
                    <input type="date" wire:model.live="endDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.department') }}</label>
                    <select wire:model.live="departmentId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">{{ __('messages.all_departments') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <button 
                    wire:click="generateReport" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    {{ __('messages.generate_report') }}
                </button>
            </div>
        </div>

        <!-- Report Preview -->
        @if($showReport && count($reportData) > 0)
            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">{{ $reportTitle }}</h3>
                    <div class="flex space-x-4">
                        <select wire:model.live="exportFormat" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        <button 
                            wire:click="exportReport" 
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            {{ __('messages.export') }}
                        </button>
                    </div>
                </div>

                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($reportColumns as $column)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $column }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reportData as $item)
                                <tr class="hover:bg-gray-50">
                                    @foreach(array_keys($reportColumns) as $field)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @php
                                                $value = 'N/A';
                                                
                                                // Handle date fields
                                                if (str_contains($field, '_at') || str_contains($field, '_date')) {
                                                    $value = $item->$field ? \Carbon\Carbon::parse($item->$field)->format('Y-m-d') : 'N/A';
                                                }
                                                // Handle item name
                                                elseif ($field === 'item' && $item->item) {
                                                    $value = $item->item->name;
                                                }
                                                // Handle item code
                                                elseif ($field === 'item_code' && $item->item) {
                                                    $value = $item->item->code;
                                                }
                                                // Handle department
                                                elseif ($field === 'department') {
                                                    $value = $item->department ? $item->department->name : 'N/A';
                                                }
                                                // Handle supplier
                                                elseif ($field === 'supplier') {
                                                    if (is_object($item->supplier)) {
                                                        $value = $item->supplier->name;
                                                    } elseif (is_string($item->supplier) && json_decode($item->supplier)) {
                                                        $supplierData = json_decode($item->supplier, true);
                                                        $value = $supplierData['name'] ?? 'N/A';
                                                    } else {
                                                        $value = $item->supplier ?? 'N/A';
                                                    }
                                                }
                                                // Handle requester
                                                elseif (($field === 'requested_by' || $field === 'requester') && $item->requester) {
                                                    $value = $item->requester->name;
                                                }
                                                // Handle unit
                                                elseif ($field === 'unit') {
                                                    $value = $item->unit ? (is_object($item->unit) ? $item->unit->name : $item->unit) : 'N/A';
                                                }
                                                // Handle numeric fields
                                                elseif (in_array($field, ['price', 'total', 'unit_price', 'quantity', 'unit_price', 'total_price'])) {
                                                    $numericValue = 0;
                                                    
                                                    // Handle total calculation
                                                    if ($field === 'total' || $field === 'total_price') {
                                                        if (isset($item->total)) {
                                                            $numericValue = (float)$item->total;
                                                        } elseif (isset($item->quantity) && isset($item->unit_price)) {
                                                            $numericValue = (float)$item->quantity * (float)$item->unit_price;
                                                        } elseif (isset($item->quantity) && isset($item->price)) {
                                                            $numericValue = (float)$item->quantity * (float)$item->price;
                                                        }
                                                    } 
                                                    // Handle unit price/price
                                                    elseif (in_array($field, ['price', 'unit_price'])) {
                                                        $numericValue = (float)($item->$field ?? $item->unit_price ?? $item->price ?? 0);
                                                    }
                                                    // Handle quantity
                                                    else {
                                                        $numericValue = (float)($item->$field ?? 0);
                                                    }
                                                    
                                                    $value = number_format($numericValue, 2, '.', '');
                                                }
                                                // Handle status
                                                elseif ($field === 'status') {
                                                    $statusClass = match($item->status) {
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                    echo "<span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$statusClass}\">" . 
                                                         __("messages.{$item->status}") . "</span>";
                                                    continue;
                                                }
                                                // Handle receipt number
                                                elseif ($field === 'receipt_number' || $field === 'receiving_number') {
                                                    $value = $item->receipt_number ?? $item->receiving_number ?? 'N/A';
                                                }
                                                // Default case
                                                else {
                                                    $value = $item->$field ?? 'N/A';
                                                }
                                            @endphp
                                            {{ $value }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($showReport)
            <div class="mt-8 p-4 bg-yellow-50 text-yellow-700 rounded-md">
                {{ __('messages.no_data_found') }}
            </div>
        @endif
    </div>
</div>