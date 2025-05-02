<div class="container mx-auto p-4">
    {{ \App\Helpers\NumberFormatter::toArabic($value) }}
    <form wire:submit.prevent="save" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <!-- Receiving Voucher Header -->
        <h1 class="text-lg font-bold mb-4 text-center">Receiving Voucher</h1>
        
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div>
            <label for="Reciving_num" class="block text-gray-700 text-sm font-bold mb-2">Receiving Number</label>
            <input type="text" id="Reciving_num" wire:model="Reciving_num"
                   class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   placeholder="receiving number">
        </div>
        <div>
            <label for="supplierSearch" class="block text-gray-700 text-sm font-bold mb-2">Company Name</label>
            <input type="text" wire:model.live="supplierSearch" id="supplierSearch" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 w-full leading-tight focus:outline-none focus:shadow-outline" placeholder="company name" />
            <ul>
                @foreach($suppliers as $supplier)
                    <li wire:click="selectSupplier({{ $supplier['id'] }})">{{ $supplier['name'] }}</li>
                @endforeach
            </ul>
        </div>
        <div>
            <label for="departmentSearch" class="block text-gray-700 text-sm font-bold mb-2">Department</label>
            <input type="text" wire:model.live="departmentSearch" id="departmentSearch" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 w-full leading-tight focus:outline-none focus:shadow-outline" placeholder="department" />
            <ul>
                @foreach($departments as $department)
                    <li wire:click="selectDepartment({{ $department['id'] }})">{{ $department['name'] }}</li>
                @endforeach
            </ul>
        </div>
        <div>
            <label for="date" class="block text-gray-700 text-sm font-bold mb-2">Date</label>
            <input type="date" id="date" wire:model="date"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div>
            <label for="taxRate" class="block text-gray-700 text-sm font-bold mb-2">Tax Rate (%)
                <input type="checkbox" wire:model.live="applyTax" class="form-checkbox" checked>
            </label>
            <input type="text" id="taxRate" wire:model="taxRate" value="14"
                   class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div>
            <label for="discountRate" class="block text-gray-700 text-sm font-bold mb-2">Discount Rate (%)
                <input type="checkbox" wire:model.live="applyDiscount" class="form-checkbox">
            </label>
            <input type="text" id="discountRate" wire:model="discountRate" value="0"
                   class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            
        </div>
    </div>
   
        <!-- Search Items -->
        <div class="mb-4">
            <div>
                <label for="itemSearch">Search Item</label>
                <input type="text" wire:model.live="itemSearch" id="itemSearch" placeholder="Search by code or name" wire:keydown.enter.prevent="selectFirstItem">
            </div>
            @if(!empty($items))
            <ul class="mt-2 space-y-2">
                @foreach ($items as $item)
                    <li class="flex justify-between items-center p-2 bg-gray-100 rounded">
                        <span>{{ $item['name'] }} - {{ $item['code'] }}</span>
                        <button type="button" wire:click="addItem({{ $item['id'] }})" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                            Add
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
        </div>
    
        <!-- Selected Items Table -->
        <div class="mb-4">
            <table class="min-w-full bg-white border rounded">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="py-2 px-4 border" style="width: 5%">ID</th>
                        <th class="py-2 px-4 border" style="width: 10%">Quantity</th>
                        <th class="py-2 px-4 border" style="width: 45%">Item</th>
                        <th class="py-2 px-4 border" style="width: 15%">Unit Price</th>
                        <th class="py-2 px-4 border" style="width: 25%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedItems as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border">{{ $index+1 }}</td>
                            <td class="py-2 px-4 border">
                                <input id="quantity-{{ $index }}" type="text" wire:model.live="selectedItems.{{ $index }}.quantity"
                                       class="w-20 text-center border rounded">
                            </td>
                            {{-- <td class="py-2 px-4 border">Unit</td> <!-- Replace with actual unit if available --> --}}
                            <td class="py-2 px-4 border">{{ $item['name'] }}</td>
                            <td class="py-2 px-4 border">
                                <input id="unit_price-{{ $index }}" type="text" value="1" wire:model.live="selectedItems.{{ $index }}.unit_price">
                            </td>
                            <td class="py-2 px-4 border">
                                {{ number_format($this->calculateTotal($index), 2) }} <!-- Use the calculateTotal method -->
                            </td>
                            <td class="py-2 px-4 border">
                                <button type="button" wire:click="removeItem({{ $index }})" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                </tbody>
            </table>
        </div>
        <!-- Subtotal, Tax, Discount, Total -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="subtotal" class="block text-gray-700 text-sm font-bold mb-2">Subtotal</label>
                <input type="text" id="subtotal" wire:model.live="subtotal" readonly
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="tax" class="block text-gray-700 text-sm font-bold mb-2">Tax</label>
                <input type="text" id="tax" wire:model.live="tax"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="discount" class="block text-gray-700 text-sm font-bold mb-2">Discount</label>
                <input type="text" id="discount" wire:model.live="discount"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="total" class="block text-gray-700 text-sm font-bold mb-2">Total</label>
                <input type="text" id="total" wire:model.live="total" readonly
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </div>
        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 focus:outline-none focus:shadow-outline">
                Save
            </button>
        </div>
    </form>
</div>
