<div class="container mx-auto p-4">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        {{-- Single Receiving Search Form --}}
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.search_and_edit_single_receiving') }}</h2>
            <div class="flex gap-4 mb-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                <input type="text" wire:model="searchReceivingNumber" wire:keydown.enter="searchReceiving"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                    placeholder="{{ __('messages.enter_receiving_number') }}">
                <button wire:click="searchReceiving"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    {{ __('messages.search') }}
                </button>
            </div>

            @if(!empty($receivingItems))
                <div class="mb-4">
                    <!-- Date, Department, and Supplier Update Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 {{ app()->getLocale() === 'ar' ? 'grid-cols-3 gap-4 mb-6' : '' }}">
                        <!-- Date -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.received_date') }}</label>
                            <div class="flex gap-2">
                                <input type="date" wire:model="date" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required>
                            </div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.department') }}</label>
                            <select wire:model="selectedDepartmentId" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">{{ __('messages.select_department') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Supplier -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.supplier') }}</label>
                            <select wire:model="selectedSupplierId" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">{{ __('messages.select_supplier') }}</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Update Button -->
                    <div class="flex justify-end mb-4 {{ app()->getLocale() === 'ar' ? 'justify-start' : '' }}">
                        <button wire:click="updateDateAndDepartment" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('messages.update_information') }}
                        </button>
                    </div>

                    <!-- Add New Item Section -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                        <h3 class="text-lg font-semibold mb-3">{{ __('messages.add_new_item') }}</h3>
                        <div class="flex flex-col md:flex-row gap-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                            <div class="flex-1">
                                <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.search_item') }}</label>
                                <input type="text" id="itemSearch" wire:model.live="itemSearch"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="{{ __('messages.search_by_item_name_or_code') }}" wire:keydown.enter.prevent="selectFirstItem">
                                @if (!empty($searchedItems))
                                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                        @foreach ($searchedItems as $item)
                                            <li wire:click="selectItem({{ $item['id'] }})"
                                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                                {{ $item['name'] }} - {{ $item['code'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <button wire:click="addNewItem" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline self-end {{ app()->getLocale() === 'ar' ? 'self-start' : '' }}">
                                {{ __('messages.add_item') }}
                            </button>
                        </div>
                    </div>

                    <!-- Remove All Button -->
                    <div class="flex justify-end mb-4 {{ app()->getLocale() === 'ar' ? 'justify-start' : '' }}">
                        <button wire:click="$wire.set('showDeleteConfirmation', true)" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('messages.remove_all_items') }}
                        </button>
                    </div>

                    @if($showDeleteConfirmation)
                        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                            <div class="bg-white p-6 rounded-lg shadow-xl {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <h3 class="text-lg font-semibold mb-4">{{ __('messages.confirm_deletion') }}</h3>
                                <p class="mb-4">{{ __('messages.are_you_sure_you_want_to_remove_all_items_from_this_receiving') }}</p>
                                <div class="flex justify-end gap-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <button wire:click="$set('showDeleteConfirmation', false)" 
                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        {{ __('messages.cancel') }}
                                    </button>
                                    <button wire:click="removeAllItems; $set('showDeleteConfirmation', false)" 
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        {{ __('messages.remove_all') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($showDeleteItemConfirmation)
                        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                            <div class="bg-white p-6 rounded-lg shadow-xl {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <h3 class="text-lg font-semibold mb-4">{{ __('messages.confirm_deletion') }}</h3>
                                <p class="mb-4">{{ __('messages.are_you_sure_you_want_to_remove_this_item_from_the_receiving') }}</p>
                                <div class="flex justify-end gap-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                    <button wire:click="$set('showDeleteItemConfirmation', null)" 
                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        {{ __('messages.cancel') }}
                                    </button>
                                    <button wire:click="removeItem({{ $showDeleteItemConfirmation }}); $set('showDeleteItemConfirmation', null)" 
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        {{ __('messages.remove') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items Table -->
                    <div class="overflow-x-auto {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                        <table class="min-w-full bg-white border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                            <thead class="bg-gray-100 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <tr>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.item') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.quantity') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.unit') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.unit_price') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.total') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.department') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.supplier') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receivingItems as $item)
                                    <tr>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @if($editingItemId === $item['id'])
                                                <select wire:model="editingItem.item_id"
                                                        class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                                    @foreach($items as $itemOption)
                                                        <option value="{{ $itemOption['id'] }}">{{ $itemOption['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $item['item']['name'] ?? 'N/A' }}
                                                @if(isset($item['item']['code']))
                                                    <div class="text-sm text-gray-500 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $item['item']['code'] }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border text-center {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @if($editingItemId === $item['id'])
                                                <input type="number" 
                                                       wire:model="editingItem.quantity" 
                                                       step="0.01"
                                                       class="w-24 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @else
                                                <div class="flex items-center justify-between {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                                    <span>{{ number_format($item['quantity'] ?? 0, 2) }}</span>
                                                    <button wire:click="editItem({{ $item['id'] }})" 
                                                            class="text-blue-600 hover:text-blue-800 {{ app()->getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @if($editingItemId === $item['id'])
                                                <select wire:model="editingItem.unit_id"
                                                        class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $item['unit']['name'] ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">
                                            @if($editingItemId === $item['id'])
                                                <input type="number" 
                                                       wire:model="editingItem.unit_price" 
                                                       step="0.01"
                                                       class="w-24 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @else
                                                {{ number_format($item['unit_price'] ?? 0, 2) }}
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">
                                            {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 2) }}
                                        </td>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            {{ $item['department']['name'] ?? 'N/A' }}
                                        </td>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            {{ $item['supplier']['name'] ?? 'N/A' }}
                                        </td>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @if($editingItemId === $item['id'])
                                                <div class="flex gap-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                                    <button wire:click="saveItemChanges" 
                                                            class="text-green-600 hover:text-green-800 {{ app()->getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="cancelEdit" 
                                                            class="text-red-600 hover:text-red-800 {{ app()->getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex gap-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                                    <button wire:click="$set('showDeleteItemConfirmation', {{ $item['id'] }})" 
                                                            class="text-red-600 hover:text-red-800 {{ app()->getLocale() === 'ar' ? 'ml-2' : 'mr-2' }}">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-end {{ app()->getLocale() === 'ar' ? 'justify-start' : '' }}">
                        <button wire:click="updateReceiving"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('messages.update_receiving') }}
                        </button>
                    </div>
                </div>
            @endif

            <hr class="my-8">

            {{-- Multiple Receiving Search Form --}}
            <div class="mb-6">
                <h2 class="text-xl font-bold mb-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.search_multiple_receivings') }}</h2>
                <div class="flex gap-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                    <input type="text" wire:model="searchNumbers"
                        class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                        placeholder="{{ __('messages.enter_receiving_numbers') }}">
                    <button wire:click="search"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ __('messages.search') }}
                    </button>
                </div>
            </div>

            {{-- Multiple Results Table --}}
            @if(!empty($receivings))
                @foreach($receivings as $receivingNumber => $items)
                    <div class="mb-8">
                        <div class="bg-gray-50 p-4 rounded-t border-b {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                            <h3 class="text-lg font-semibold">{{ __('messages.receiving_number') }} {{ $receivingNumber }}</h3>
                            <div class="text-sm text-gray-600 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <span>{{ __('messages.received_date') }}: {{ date('Y-m-d', strtotime($date)) }}</span>
                                <span class="mx-4">|</span>
                                <span>{{ __('messages.department') }}: {{ $items['department']['name'] }}</span>
                                <span class="mx-4">|</span>
                                <span>{{ __('messages.supplier') }}: {{ $items['supplier']['name'] }}</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                            <table class="min-w-full bg-white border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <thead class="bg-gray-100 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                    <tr>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.item') }}</th>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.quantity') }}</th>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.unit') }}</th>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.unit_price') }}</th>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.subtotal') }}</th>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.tax') }}</th>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.discount') }}</th>
                                        <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $receivingSubtotal = 0;
                                        $receivingTax = 0;
                                        $receivingDiscount = 0;
                                    @endphp
                                    @foreach($items['items'] as $item)
                                        @php
                                            $itemSubtotal = $item['quantity'] * $item['unit_price'];
                                            $itemTax = $itemSubtotal * ($item['tax'] / 100);
                                            $itemDiscount = $itemSubtotal * ($item['discount'] / 100);
                                            $itemTotal = $itemSubtotal + $itemTax - $itemDiscount;
                                            $receivingSubtotal += $itemSubtotal;
                                            $receivingTax += $itemTax;
                                            $receivingDiscount += $itemDiscount;
                                        @endphp
                                        <tr>
                                            <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $item['item']['name'] }}</td>
                                            <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ number_format($item['quantity'], 2) }}</td>
                                            <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ $item['unit']['name'] }}</td>
                                            <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ number_format($item['unit_price'], 2) }}</td>
                                            <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ number_format($itemSubtotal, 2) }}</td>
                                            <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ number_format($itemTax, 2) }}</td>
                                            <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ number_format($itemDiscount, 2) }}</td>
                                            <td class="py-2 px-4 border text-right {{ app()->getLocale() === 'ar' ? 'text-left' : 'text-right' }}">{{ number_format($itemTotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-gray-50 font-semibold {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                        <td colspan="4" class="py-2 px-4 border text-right">{{ __('messages.receiving_total') }}:</td>
                                        <td class="py-2 px-4 border text-right">{{ number_format($receivingSubtotal, 2) }}</td>
                                        <td class="py-2 px-4 border text-right">{{ number_format($receivingTax, 2) }}</td>
                                        <td class="py-2 px-4 border text-right">{{ number_format($receivingDiscount, 2) }}</td>
                                        <td class="py-2 px-4 border text-right">{{ number_format($receivingSubtotal + $receivingTax - $receivingDiscount, 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                {{-- Grand Totals --}}
                <div class="mt-8 bg-gray-100 p-6 rounded shadow-sm {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                    <h3 class="text-lg font-semibold mb-4">{{ __('messages.summary_for_all_receivings') }}</h3>
                    <div class="grid grid-cols-2 gap-4 max-w-md {{ app()->getLocale() === 'ar' ? 'grid-cols-2 gap-4 max-w-md' : '' }}">
                        <div class="font-semibold text-right">{{ __('messages.total_subtotal') }}:</div>
                        <div class="text-right">{{ number_format($totalSubtotal, 2) }}</div>
                        <div class="font-semibold text-right">{{ __('messages.total_tax') }}:</div>
                        <div class="text-right">{{ number_format($totalTax, 2) }}</div>
                        <div class="font-semibold text-right">{{ __('messages.total_discount') }}:</div>
                        <div class="text-right">{{ number_format($totalDiscount, 2) }}</div>
                        <div class="font-semibold text-right border-t pt-2">{{ __('messages.grand_total') }}:</div>
                        <div class="text-right border-t pt-2 text-lg">{{ number_format($grandTotal, 2) }}</div>
                    </div>
                </div>
            @endif

            {{-- Flash Messages --}}
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>