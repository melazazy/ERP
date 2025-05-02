<div class="container mx-auto mt-5">
    {{-- <h1 class="text-3xl font-bold mb-6 text-center">Reports</h1>

    <h2 class="text-2xl font-semibold mb-4">Requisitions</h2>
    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Item</th>
                <th class="py-3 px-4 border-b text-left">Department</th>
                <th class="py-3 px-4 border-b text-left">Quantity</th>
                <th class="py-3 px-4 border-b text-left">Requested By</th>
                <th class="py-3 px-4 border-b text-left">Requested Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requisitions as $requisition)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $requisition->item->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->department->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->quantity }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->requester->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->requested_date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="text-2xl font-semibold mb-4 mt-6">Trusts</h2>
    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Item</th>
                <th class="py-3 px-4 border-b text-left">Department</th>
                <th class="py-3 px-4 border-b text-left">Quantity</th>
                <th class="py-3 px-4 border-b text-left">Requested By</th>
                <th class="py-3 px-4 border-b text-left">Requested Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($trusts as $trust)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $trust->item->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $trust->department->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $trust->quantity }}</td>
                    <td class="py-2 px-4 border-b">{{ $trust->requester->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $trust->requested_date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="text-2xl font-semibold mb-4 mt-6">Receivings</h2>
    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Item</th>
                <th class="py-3 px-4 border-b text-left">Supplier</th>
                <th class="py-3 px-4 border-b text-left">Department</th>
                <th class="py-3 px-4 border-b text-left">Quantity</th>
                <th class="py-3 px-4 border-b text-left">Received Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receivings as $receiving)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $receiving->item->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $receiving->supplier->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $receiving->department->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $receiving->quantity }}</td>
                    <td class="py-2 px-4 border-b">{{ $receiving->received_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table> --}}
    <!-- Item Selection Section -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg shadow-sm">
        <label for="itemSearch" class="block mb-2 font-medium text-gray-700">
            اختر الصنف:
        </label>
        
        <div class="relative">
            <input type="text" 
                id="itemSearch"
                wire:model.live="itemSearch"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                placeholder="ابحث عن طريق اسم الصنف او الكود">
            
            @if (!empty($items) && !$selectedItem)
                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                    @foreach ($items as $item)
                        <div class="cursor-pointer p-3 hover:bg-gray-100 border-b border-gray-200"
                            wire:click="selectItem({{ $item->id }})">
                            <div class="font-medium">{{ $item->name }}</div>
                            <div class="text-sm text-gray-600">كود: {{ $item->code }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Item Movements Report -->
    @if (!empty($itemMovements))
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">رقم المستند</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع الحركة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">وارد</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">منصرف</th>
                                {{-- <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الكمية</th> --}}
                                {{-- <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">سعر الوحدة</th> --}}
                                {{-- <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الضريبة %</th> --}}
                                {{-- <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الخصم %</th> --}}
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجمالي</th>
                                {{-- <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">البيان</th> --}}
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($itemMovements as $movement)
                                <tr class="{{ $movement['type'] === 'in' ? 'bg-green-50' : 'bg-red-50' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement['date'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement['document_number'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $movement['type'] === 'in' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $movement['type'] === 'in' ? 'وارد' : 'منصرف' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement['type'] === 'in' ? number_format($movement['quantity'], 2) : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement['type'] === 'out' ? number_format($movement['quantity'], 2) : '-' }}</td>
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($movement['unit_price'], 2) }}</td> --}}
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement['tax'] }}</td> --}}
                                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement['discount'] }}</td> --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($movement['total'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $movement['description'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <p class="text-gray-600">No movements found for this item.</p>
                </div>
            </div>
        </div>
    @endif
</div>