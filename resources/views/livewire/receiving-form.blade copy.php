{{-- <div>
    <main>
        <div id="details" class="clearfix">
            <div id="client">
                <div class="to">INVOICE TO:</div>
                <h2 class="name">{{ $companyName }}</h2>
                <div class="address">{{ $address }}</div>
                <div class="email"><a href="mailto:{{ $email }}">{{ $email }}</a></div>
            </div>
            <div id="invoice">
                <h1>INVOICE {{ $invoiceNumber }}</h1>
                <div class="date">Date of Invoice: {{ $date }}</div>
                <div class="date">Due Date: {{ $dueDate }}</div>
            </div>
        </div>

        <!-- Search Input for Items -->
        <div>
            <label for="itemSearch">Search Item</label>
            <input type="text" wire:model="itemSearch" id="itemSearch" wire:keyup="searchItems" placeholder="Search by code or name">
            <!-- Add logic to display search results and select an item -->
        </div>

        <table border="0" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th class="no">#</th>
                    <th class="desc">DESCRIPTION</th>
                    <th class="unit">UNIT PRICE</th>
                    <th class="qty">QUANTITY</th>
                    <th class="total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $index => $item)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td class="desc">
                        <h3>{{ $item['description'] }}</h3>
                        {{ $item['details'] }}
                    </td>
                    <td class="unit">${{ number_format($item['unit_price'], 2) }}</td>
                    <td class="qty">{{ $item['quantity'] }}</td>
                    <td class="total">${{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">SUBTOTAL</td>
                    <td>${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">TAX {{ $taxRate }}%</td>
                    <td>${{ number_format($tax, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2">GRAND TOTAL</td>
                    <td>${{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <div id="thanks">Thank you!</div>
        <div id="notices">
            <div>NOTICE:</div>
            <div class="notice">A finance charge of 1.5% will be made on unpaid balances after 30 days.</div>
        </div>
    </main>
</div> --}}

<div class="container mx-auto p-4">
    <form wire:submit.prevent="save" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <!-- Receiving Voucher Header -->
        <h1 class="text-lg font-bold mb-4">Receiving Voucher</h1>
    
        <!-- Receiving Number, Company Name, Date, Department -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="Reciving_num" class="block text-gray-700 text-sm font-bold mb-2">Receiving Number</label>
                <input type="text" id="Reciving_num" wire:model="Reciving_num"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       placeholder="Enter receiving number">
            </div>
            <div>
                <label for="date" class="block text-gray-700 text-sm font-bold mb-2">Date</label>
                <input type="date" id="date" wire:model="date"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            {{-- <div>
                <label for="companyName" class="block text-gray-700 text-sm font-bold mb-2">Company Name</label>
                <input type="text" id="companyName" wire:model="companyName"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       placeholder="Enter company name">
            </div> --}}
            <div>
                <label for="supplierSearch">Company Name</label>
                <input type="text" wire:model.live="supplierSearch" id="supplierSearch" placeholder="Search for a supplier" />
                <ul>
                    @foreach($suppliers as $supplier)
                        <li wire:click="selectSupplier({{ $supplier['id'] }})">{{ $supplier['name'] }}</li>
                    @endforeach
                </ul>
            </div>
            <div>
                <label for="departmentSearch">Department</label>
                <input type="text" wire:model.live="departmentSearch" id="departmentSearch" placeholder="Search for a department" />
                <ul>
                    @foreach($departments as $department)
                        <li wire:click="selectDepartment({{ $department['id'] }})">{{ $department['name'] }}</li>
                    @endforeach
                </ul>
            </div>
            
            {{-- <div>
                <label for="department" class="block text-gray-700 text-sm font-bold mb-2">Department</label>
                <input type="text" id="department" wire:model="department"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       placeholder="Enter department">
            </div> --}}
        </div>
    
        <!-- Search Items -->
        <div class="mb-4">
            {{-- <label for="searchTerm" class="block text-gray-700 text-sm font-bold mb-2">Search Items</label>
            <input type="text" id="searchTerm" wire:model.live="searchTerm"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                   placeholder="Search by item name or code"> --}}
           {{-- <div>
               <label for="itemSearch">Search Item</label>
               <input type="text" wire:model="itemSearch" id="itemSearch" wire:keyup="updatedSearchTerm" placeholder="Search by code or name">
           </div> --}}
           {{-- <div>
            <label for="itemSearch">Search Item</label>
            <input type="text" wire:model.live="itemSearch" id="itemSearch" placeholder="Search by code or name"wire:keydown.enter="selectFirstItem" >
        </div>
        @if(!empty($items))
                   <ul class="mt-2 space-y-2">
                @foreach ($items as $item)
                    <li class="flex justify-between items-center p-2 bg-gray-100 rounded">
                        <span>{{ $item['name'] }} - {{ $item['code'] }}</span>
                        <button type="button" wire:click="addItem({{ $item['id'] }})"
                                class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                            Add
                        </button>
                    </li>
                @endforeach
            </ul>
        @endIf --}}
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
                                <input id="quantity-{{ $index }}" type="number" wire:model.live="selectedItems.{{ $index }}.quantity"
                                       class="w-20 text-center border rounded">
                            </td>
                            {{-- <td class="py-2 px-4 border">Unit</td> <!-- Replace with actual unit if available --> --}}
                            <td class="py-2 px-4 border">{{ $item['name'] }}</td>
                            <td class="py-2 px-4 border">
                                <input id="unit_price-{{ $index }}" type="number" value="1" wire:model.live="selectedItems.{{ $index }}.unit_price">
                            </td>
                            <td class="py-2 px-4 border">
                                {{ number_format($this->calculateTotal($index), 2) }} <!-- Use the calculateTotal method -->
                            </td>
                            {{-- <td class="py-2 px-4 border" id="total-{{ $index }}">{{ number_format($item['quantity'] * $item['unit_price'], 2) }}</td> --}}
                        </tr>
                        @endforeach
                        {{-- <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border">
                                <input type="number" wire:model="" 
                                       class="text-center border rounded">
                            </td>
                            <td class="py-2 px-4 border">
                                <input type="text" wire:model=""
                                       class="text-center border rounded">
                            </td>
                            <td class="py-2 px-4 border">
                                <input type="number" wire:model=""
                                       class="text-center border rounded">
                            </td>
                            <td class="py-2 px-4 border">
                                <input type="number" wire:model=""
                                       class="text-center border rounded">
                            </td>
                        </tr> --}}
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
                <input type="number" id="tax" wire:model.live="tax"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="discount" class="block text-gray-700 text-sm font-bold mb-2">Discount</label>
                <input type="number" id="discount" wire:mode.live="discount"
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
