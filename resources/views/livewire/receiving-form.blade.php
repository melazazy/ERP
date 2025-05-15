<div>
    {{-- Search/Edit Receiving Voucher Section --}}
    <section>
        {{-- Error Message --}}
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Success Message --}}
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('message') }}
            </div>
        @endif
    </section>
    {{-- Create Receiving Voucher Section --}}
    <section class="mb-8">
        <form wire:submit.prevent="save" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
            {{-- Receiving Voucher Header --}}
            <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">{{ __('messages.create_receiving_voucher') }}</h1>

            {{-- Top Fields --}}
            <div class="flex flex-wrap items-center gap-4 mb-6">
                {{-- Receiving Number --}}
                <div class="flex-1 min-w-[200px]">
                    <label for="receiving_num" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.receiving_number') }}</label>
                    <input type="text" id="receiving_num" wire:model="receiving_num"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="{{ __('messages.receiving_number_placeholder') }}">
                </div>

                {{-- Supplier Search --}}
                <div>
                    <label for="supplierSearch" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.company_name') }}</label>
                    <input type="text" wire:model.live="supplierSearch" id="supplierSearch"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="{{ __('messages.company_name_placeholder') }}">
                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                        @if ($supplierSearch)
                            @foreach ($suppliers as $supplier)
                                <li wire:click="selectSupplier({{ $supplier['id'] }})"
                                    class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                    {{ $supplier['name'] }}
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                {{-- Department Search --}}
                <div>
                    <label for="departmentSearch" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.department') }}</label>
                    <input type="text" wire:model.live="departmentSearch" id="departmentSearch"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="{{ __('messages.department_placeholder') }}">
                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                        @if ($departmentSearch)
                            @foreach ($departments as $department)
                                <li wire:click="selectDepartment({{ $department['id'] }})"
                                    class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                    {{ $department['name'] }}
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                {{-- Date --}}
                <div class="flex-1 min-w-[200px]">
                    <label for="date" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.date') }}</label>
                    <input type="date" id="date" wire:model="date"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Tax Rate and Search Items --}}
            <div class="flex flex-col md:flex-row gap-4 mb-6 justify-between">
                {{-- Left Side: Search Items --}}
                <div class="w-full md:w-1/2">
                    <div>
                        <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.search_item') }}</label>
                        <input type="text" wire:model.live="itemSearch" id="itemSearch"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="{{ __('messages.search_by_code_or_name') }}" wire:keydown.enter.prevent="selectFirstItem">
                        @if (!empty($items))
                            <ul
                                class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                                @foreach ($items as $item)
                                    <li class="flex justify-between items-center p-2 hover:bg-blue-100 cursor-pointer">
                                        <span>{{ $item['name'] }} - {{ $item['code'] }}</span>
                                        <button type="button" wire:click="addItem({{ $item['id'] }})"
                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                            {{ __('messages.add') }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                {{-- Right Side: Tax and Discount --}}
                <div class="w-full md:w-1/2">
                    <div class="flex flex-col md:flex-row gap-4 justify-end">
                        {{-- Tax Rate --}}
                        <div class="w-full md:flex-1 max-w-[200px]">
                            <label for="taxRate" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.tax_rate') }}
                                (%)</label>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.live="applyTax"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <input type="number" id="taxRate" wire:model="taxRate" value="14"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 ml-2">
                            </div>
                        </div>

                        {{-- Discount Rate --}}
                        <div class="w-full md:flex-1 max-w-[200px]">
                            <label for="discountRate" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.discount_rate') }}
                                (%)</label>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model.live="applyDiscount"
                                    class="form-checkbox h-5 w-5 text-blue-600">
                                <input type="number" id="discountRate" wire:model="discountRate" value="0"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 ml-2">
                            </div>
                        </div>

                        {{-- Auto Create Requisition --}}
                        <div class="w-full md:flex-1 max-w-[200px]">
                            <label for="createRequisition" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.auto_create_requisition') }}</label>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="createRequisition" id="createRequisition"
                                    class="form-checkbox h-4 w-4 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">{{ __('messages.create_dir_requisition') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Selected Items Table --}}
            <div class="mb-6 overflow-x-auto"> {{-- Added overflow-x-auto for horizontal scrolling --}}
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.id') }}</th>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.item_code') }}</th>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.quantity') }}</th>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.item') }}</th>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.unit_price') }}</th>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.unit') }}</th>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.total') }}</th>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($selectedItems as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ $index + 1 }}</td>
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ $item['code'] }}</td>
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <input type="number" step="0.0001"
                                        wire:model.live="selectedItems.{{ $index }}.quantity"
                                        class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </td>
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}" dir="rtl">{{ $item['name'] }}</td>
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <input type="number" step="0.0001"
                                        wire:model.live="selectedItems.{{ $index }}.unit_price"
                                        class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </td>
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <select wire:model.live="selectedItems.{{ $index }}.unit_id"
                                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit['id'] }}" 
                                                {{ $item['unit_id'] == $unit['id'] ? 'selected' : '' }}>
                                                {{ $unit['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('selectedItems.'.$index.'.unit_id') 
                                        <span class="text-red-500 text-xs">{{ $message }}</span> 
                                    @enderror
                                </td>
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ number_format($selectedItems[$index]['quantity'] * $selectedItems[$index]['unit_price'], 2) }}
                                </td>
                                <td class="py-2 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <button type="button" wire:click="removeItem({{ $index }})"
                                        class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                        {{ __('messages.delete') }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        @if (count($selectedItems) === 0)
                            <tr>
                                <td colspan="8" class="py-2 px-4 border text-center">{{ __('messages.no_items_selected') }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Summary Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="subtotal" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.subtotal') }}</label>
                    <input type="text" id="subtotal" wire:model.live="subtotal" readonly
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="tax" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.tax') }}</label>
                    <input type="text" id="tax" wire:model.live="tax" readonly
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="discount" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.discount') }}</label>
                    <input type="text" id="discount" wire:model.live="discount" readonly
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="total" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.total') }}</label>
                    <input type="text" id="total" wire:model.live="total" readonly
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-end">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    {{ __('messages.save_voucher') }}
                </button>
            </div>
        </form>
    </section>


</div>
