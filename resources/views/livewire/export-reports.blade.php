<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Export Reports</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form wire:submit.prevent="generateReport">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 mb-2">Report Type</label>
                    <select wire:model="reportType" class="w-full border rounded px-3 py-2">
                        <option value="receivings">Receivings</option>
                        <option value="requisitions">Requisitions</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">Start Date</label>
                    <input type="date" wire:model="startDate" class="w-full border rounded px-3 py-2">
                    @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-gray-700 mb-2">End Date</label>
                    <input type="date" wire:model="endDate" class="w-full border rounded px-3 py-2">
                    @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Generate Report
            </button>
        </form>
    </div>

    @if($showReport)
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Generated Report</h2>
            <button wire:click="export" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                Download Excel
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-4 border">Receiving #</th>
                        <th class="py-2 px-4 border">Item</th>
                        <th class="py-2 px-4 border">Qty</th>
                        <th class="py-2 px-4 border">Unit</th>
                        <th class="py-2 px-4 border">Unit Price</th>
                        <th class="py-2 px-4 border">Total</th>
                        <th class="py-2 px-4 border">Supplier</th>
                        <th class="py-2 px-4 border">Department</th>
                        <th class="py-2 px-4 border">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData as $row)
                    <tr>
                        <td class="py-2 px-4 border">{{ $row['receiving_number'] }}</td>
                        <td class="py-2 px-4 border">{{ $row['item'] }}</td>
                        <td class="py-2 px-4 border">{{ $row['quantity'] }}</td>
                        <td class="py-2 px-4 border">{{ $row['unit'] }}</td>
                        <td class="py-2 px-4 border">{{ number_format($row['unit_price'], 2) }}</td>
                        <td class="py-2 px-4 border">{{ number_format($row['total'], 2) }}</td>
                        <td class="py-2 px-4 border">{{ $row['supplier'] }}</td>
                        <td class="py-2 px-4 border">{{ $row['department'] }}</td>
                        <td class="py-2 px-4 border">{{ $row['date'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>