<div class="container mx-auto p-4">
    <form wire:submit.prevent="save" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <!-- Receiving Voucher Header -->
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Receiving Voucher</h1>

        <!-- Top Fields -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Receiving Number -->
            <div>
                <label for="Reciving_num" class="block text-gray-700 text-sm font-bold mb-2">Receiving Number</label>
                <input type="text" id="Reciving_num" wire:model="Reciving_num"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Receiving number">
            </div>

            <!-- Supplier Search -->
            <div>
                <label for="supplierSearch" class="block text-gray-700 text-sm font-bold mb-2">Company Name</label>
                <input type="text" wire:model.live="supplierSearch" id="supplierSearch"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Company name">
                <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                    @foreach ($suppliers as $supplier)
                        <li wire:click="selectSupplier({{ $supplier['id'] }})"
                            class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                            {{ $supplier['name'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
            

            <!-- Department Search -->
            <div>
                <label for="departmentSearch" class="block text-gray-700 text-sm font-bold mb-2">Department</label>
                <input type="text" wire:model.live="departmentSearch" id="departmentSearch"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Department">
                <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                    @foreach ($departments as $department)
                        <li wire:click="selectDepartment({{ $department['id'] }})"
                            class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                            {{ $department['name'] }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Date -->
            <div>
                <label for="date" class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                <input type="date" id="date" wire:model="date"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Tax Rate -->
            <div>
                <label for="taxRate" class="block text-gray-700 text-sm font-bold mb-2">Tax Rate (%)</label>
                <div class="flex items-center">
                    <input type="checkbox" wire:model.live="applyTax" class="form-checkbox h-5 w-5 text-blue-600">
                    <input type="text" id="taxRate" wire:model="taxRate" value="14"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 ml-2">
                </div>
            </div>

            <!-- Discount Rate -->
            <div>
                <label for="discountRate" class="block text-gray-700 text-sm font-bold mb-2">Discount Rate (%)</label>
                <div class="flex items-center">
                    <input type="checkbox" wire:model.live="applyDiscount" class="form-checkbox h-5 w-5 text-blue-600">
                    <input type="text" id="discountRate" wire:model="discountRate" value="0"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 ml-2">
                </div>
            </div>
        </div>

        <!-- Search Items -->
        <div class="mb-6">
            <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2">Search Item</label>
            <input type="text" wire:model.live="itemSearch" id="itemSearch"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Search by code or name" wire:keydown.enter.prevent="selectFirstItem">
            @if (!empty($items))
                <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                    @foreach ($items as $item)
                        <li class="flex justify-between items-center p-2 hover:bg-blue-100 cursor-pointer">
                            <span>{{ $item['name'] }} - {{ $item['code'] }}</span>
                            <button type="button" wire:click="addItem({{ $item['id'] }})"
                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                Add
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Selected Items Table -->
        <div class="mb-6">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4 border text-left">ID</th>
                        <th class="py-3 px-4 border text-left">Quantity</th>
                        <th class="py-3 px-4 border text-left">Item</th>
                        <th class="py-3 px-4 border text-left">Unit Price</th>
                        <th class="py-3 px-4 border text-left">Total</th>
                        <th class="py-3 px-4 border text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedItems as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border">{{ $index + 1 }}</td>
                            <td class="py-2 px-4 border">
                                <input type="text" wire:model.live="selectedItems.{{ $index }}.quantity"
                                    class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="py-2 px-4 border">{{ $item['name'] }}</td>
                            <td class="py-2 px-4 border">
                                <input type="text" wire:model.live="selectedItems.{{ $index }}.unit_price"
                                    class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="py-2 px-4 border">{{ number_format($this->calculateTotal($index), 2) }}</td>
                            <td class="py-2 px-4 border">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Subtotal, Tax, Discount, Total -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="subtotal" class="block text-gray-700 text-sm font-bold mb-2">Subtotal</label>
                <input type="text" id="subtotal" wire:model.live="subtotal" readonly
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="tax" class="block text-gray-700 text-sm font-bold mb-2">Tax</label>
                <input type="text" id="tax" wire:model.live="tax"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="discount" class="block text-gray-700 text-sm font-bold mb-2">Discount</label>
                <input type="text" id="discount" wire:model.live="discount"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="total" class="block text-gray-700 text-sm font-bold mb-2">Total</label>
                <input type="text" id="total" wire:model.live="total" readonly
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit"
                class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                Save
            </button>
        </div>
    </form>
    <div>
        <!-- Search Form -->
        <div class="mb-6 bg-white shadow-md rounded px-8 pt-6 pb-8">
            <h2 class="text-xl font-bold mb-4">Search Receiving</h2>
            <div class="flex gap-4">
                <input type="text" 
                       wire:model="searchReceivingNumber"
                       class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                       placeholder="Enter Receiving Number">
                <button wire:click="searchReceiving"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Search
                </button>
            </div>
        </div>
    
        @if(session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif
    
        @if($editMode)
            <form wire:submit.prevent="updateReceiving" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                        <input type="date" wire:model="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Department</label>
                        <select wire:model="selectedDepartment" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Supplier</label>
                        <select wire:model="selectedSupplier" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
    
                <!-- Items Table -->
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b">Item</th>
                            <th class="py-2 px-4 border-b">Quantity</th>
                            <th class="py-2 px-4 border-b">Unit Price</th>
                            <th class="py-2 px-4 border-b">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receivingItems as $index => $item)
                            <tr>
                                <td class="py-2 px-4 border-b">{{ $item['name'] }}</td>
                                <td class="py-2 px-4 border-b">
                                    <input type="number" 
                                           wire:model="receivingItems.{{ $index }}.quantity"
                                           class="shadow appearance-none border rounded w-24 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <input type="number" 
                                           wire:model="receivingItems.{{ $index }}.unit_price"
                                           class="shadow appearance-none border rounded w-24 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                </td>
                                <td class="py-2 px-4 border-b">{{ $item['quantity'] * $item['unit_price'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
    
                <div class="flex justify-end mt-4">
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Receiving
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
