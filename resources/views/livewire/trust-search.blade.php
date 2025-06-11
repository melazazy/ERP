<!-- resources/views/livewire/trust-search.blade.php -->
<div class="py-12 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">
            <!-- Title -->
            <h2 class="text-2xl font-semibold mb-6">{{ __('messages.trusts') }}</h2>

            <!-- Search and Filter Section -->
            <div class="mb-6 flex flex-col {{ app()->getLocale() === 'ar' ? 'items-end' : 'items-start' }} space-y-4 md:flex-row md:justify-between md:items-center md:space-y-0">
                <!-- Date Range Picker -->
                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.date_range') }}</label>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <input type="date" wire:model.live="startDate" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="flex items-center justify-center text-gray-500">{{ __('messages.to') }}</span>
                        <input type="date" wire:model.live="endDate" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="w-full md:w-64">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.status') }}</label>
                    <select wire:model.live="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="pending">{{ __('messages.pending') }}</option>
                        <option value="approved">{{ __('messages.approved') }}</option>
                        <option value="rejected">{{ __('messages.rejected') }}</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="w-full md:w-64">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.search') }}</label>
                    <div class="relative">
                        <input type="text" wire:model.live="search" id="search" placeholder="{{ __('messages.search_placeholder') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 {{ app()->getLocale() === 'ar' ? 'pr-10' : 'pl-10' }}">
                        <div class="absolute inset-y-0 {{ app()->getLocale() === 'ar' ? 'left-0 pl-3' : 'right-0 pr-3' }} flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Update Button -->
                <div class="w-full md:w-auto mt-6 md:mt-0">
                    <button wire:click="updateSearch" 
                            class="w-full md:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ __('messages.update_information') }}
                    </button>
                </div>
            </div>

            {{-- Single Trust Search Form --}}
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4">{{ __('messages.search_and_edit_single_trust') }}</h2>
                <div class="flex gap-4 mb-4">
                    <input type="text" wire:model="searchTrustNumber" wire:keydown.enter="searchTrust"
                        class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                        placeholder="{{ __('messages.enter_trust_number') }}">
                    <button wire:click="searchTrust"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ __('messages.search') }}
                    </button>
                </div>

                @if(!empty($trustItems))
                    <div class="mb-4">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.requested_date') }}</label>
                                <div class="flex gap-2">
                                    <input type="date" wire:model="date" 
                                        value="{{ $date }}"
                                        class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                                        required>
                                    <button wire:click="updateDateAndDepartment" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                        {{ __('messages.update') }}
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.department') }}</label>
                                <div class="flex gap-2">
                                    <select wire:model="selectedDepartmentId" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <option value="">{{ __('messages.select_department') }}</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Add New Item Section -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold mb-3">{{ __('messages.add_new_item') }}</h3>
                            <div class="flex flex-col md:flex-row gap-4">
                                <div class="flex-1">
                                    <label for="itemSearch" class="sr-only">{{ __('messages.search_item') }}</label>
                                    <div class="relative" x-data="{ isOpen: false }" @click.away="isOpen = false">
                                        <input 
                                            id="itemSearch"
                                            type="text" 
                                            wire:model.live.debounce.300ms="itemSearch"
                                            x-on:focus="isOpen = true"
                                            class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 p-2"
                                            placeholder="{{ __('messages.search_by_item_name_or_code') }}"
                                            aria-haspopup="listbox"
                                            aria-expanded="true"
                                            aria-autocomplete="list"
                                            autocomplete="off"
                                        >
                                        @if(!empty($itemSearch))
                                            <button 
                                                type="button" 
                                                wire:click="$set('itemSearch', '')" 
                                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                                                aria-label="{{ __('messages.clear_search') }}"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        @endif

                                        @if(!empty($itemSearch))
                                            <div 
                                                x-show="isOpen"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                class="absolute z-10 w-full bg-white border rounded shadow-lg max-h-60 overflow-auto mt-1"
                                                role="listbox"
                                            >
                                                @forelse($searchedItems as $searchedItem)
                                                    <div 
                                                        wire:click="$set('selectedItemId', {{ $searchedItem['id'] }}); $set('itemSearch', '{{ addslashes($searchedItem['name']) }}'); isOpen = false"
                                                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b last:border-0 flex items-center justify-between"
                                                        role="option"
                                                        :aria-selected="{{ $selectedItemId == $searchedItem['id'] ? 'true' : 'false' }}"
                                                    >
                                                        <div class="flex-1">
                                                            <div class="font-medium">{{ $searchedItem['name'] }}</div>
                                                            <div class="text-sm text-gray-500">{{ $searchedItem['code'] }}</div>
                                                        </div>
                                                        @if(isset($searchedItem['available_quantity']))
                                                            <span class="text-sm {{ $searchedItem['available_quantity'] <= 0 ? 'text-red-600' : 'text-gray-600' }}">
                                                                {{ $searchedItem['available_quantity'] <= 0 ? __('messages.out_of_stock') : $searchedItem['available_quantity'] . ' ' . __('messages.available') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <div class="px-3 py-2 text-gray-500">
                                                        {{ __('messages.no_items_found') }}
                                                    </div>
                                                @endforelse
                                            </div>
                                        @endif
                                    </div>
                                    @error('selectedItemId')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button 
                                    wire:click="addNewItem" 
                                    wire:loading.attr="disabled"
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline self-start flex items-center"
                                    :class="{ 'opacity-50 cursor-not-allowed': !$wire.selectedItemId }"
                                    {{ !$selectedItemId ? 'disabled' : '' }}
                                >
                                    <span wire:loading wire:target="addNewItem" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="addNewItem">
                                        {{ __('messages.add_item') }}
                                    </span>
                                    <span wire:loading wire:target="addNewItem">
                                        {{ __('messages.adding') }}...
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Remove All Button -->
                        <div class="flex justify-end mb-4">
                            <button wire:click="$set('showDeleteConfirmation', true)" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                {{ __('messages.remove_all_items') }}
                            </button>
                        </div>

                        @if($showDeleteConfirmation ?? false)
                            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                                <div class="bg-white p-6 rounded-lg shadow-xl">
                                    <h3 class="text-lg font-semibold mb-4">{{ __('messages.confirm_deletion') }}</h3>
                                    <p class="mb-4">{{ __('messages.are_you_sure_you_want_to_remove_all_items_from_this_trust') }}</p>
                                    <div class="flex justify-end gap-2">
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

                        @if($searchTrustNumber)
                            <!-- Single Trust View -->
                            <table class="min-w-full bg-white border">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 border">{{ __('messages.item_name') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.item_code') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.quantity') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.department') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.requested_by') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.status') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trustItems as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-4 border">{{ $item['item']['name'] }}</td>
                                            <td class="py-2 px-4 border">{{ $item['item']['code'] }}</td>
                                            <td class="py-2 px-4 border">
                                                @if($editingItemId === $item['id'])
                                                    <input type="number" 
                                                           wire:model="editingItem.quantity" 
                                                           class="w-20 border rounded px-2 py-1"
                                                           min="1">
                                                @else
                                                    {{ $item['quantity'] }}
                                                @endif
                                            </td>
                                            <td class="py-2 px-4 border">{{ $item['department']['name'] }}</td>
                                            <td class="py-2 px-4 border">{{ $item['requested_by']['name'] }}</td>
                                            <td class="py-2 px-4 border">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $item['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                       ($item['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                       'bg-yellow-100 text-yellow-800') }}">
                                                    {{ $item['status'] }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 border">
                                                <div class="flex space-x-2">
                                                    @if($editingItemId === $item['id'])
                                                        <button wire:click="saveItemChanges" 
                                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-sm">
                                                            {{ __('messages.save') }}
                                                        </button>
                                                        <button wire:click="cancelEdit" 
                                                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-1 px-2 rounded text-sm">
                                                            {{ __('messages.cancel') }}
                                                        </button>
                                                    @else
                                                        <button wire:click="editItem({{ $item['id'] }})" 
                                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-sm">
                                                            {{ __('messages.edit') }}
                                                        </button>
                                                        <button wire:click="$set('showDeleteItemConfirmation', {{ $item['id'] }})" 
                                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-sm">
                                                            {{ __('messages.delete') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <!-- Date Range Search View -->
                            <table class="min-w-full bg-white border">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 border">{{ __('messages.requisition_number') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.requested_date') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.department') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.requested_by') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.total_items') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.status') }}</th>
                                        <th class="py-2 px-4 border">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trustItems as $trust)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-4 border">
                                                <button wire:click="searchTrust('{{ $trust['requisition_number'] }}')"
                                                        class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $trust['requisition_number'] }}
                                                </button>
                                            </td>
                                            <td class="py-2 px-4 border">{{ date('Y-m-d', strtotime($trust['requested_date'])) }}</td>
                                            <td class="py-2 px-4 border">{{ $trust['department']['name'] }}</td>
                                            <td class="py-2 px-4 border">{{ $trust['requested_by']['name'] }}</td>
                                            <td class="py-2 px-4 border">
                                                <span class="font-semibold">{{ $trust['total_items'] }}</span> items
                                                <span class="text-gray-500">(Total: {{ $trust['total_quantity'] }})</span>
                                            </td>
                                            <td class="py-2 px-4 border">
                                                @foreach($trust['statuses'] as $status)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                           ($status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                                           'bg-yellow-100 text-yellow-800') }}">
                                                        {{ $status }}
                                                    </span>
                                                @endforeach
                                            </td>
                                            <td class="py-2 px-4 border">
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click="open = !open" 
                                                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200 focus:outline-none">
                                                        {{ __('messages.view_items') }}
                                                    </button>
                                                    
                                                    <div x-show="open" 
                                                         @click.away="open = false"
                                                         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl z-50">
                                                        <div class="p-4">
                                                            <h3 class="text-lg font-semibold mb-2">{{ __('messages.items_in_trust') }}</h3>
                                                            <div class="space-y-2">
                                                                @foreach($trust['items'] as $item)
                                                                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                                                        <div>
                                                                            <div class="font-medium">{{ $item['item']['name'] }}</div>
                                                                            <div class="text-sm text-gray-500">{{ $item['item']['code'] }}</div>
                                                                        </div>
                                                                        <div class="text-right">
                                                                            <div class="font-medium">Qty: {{ $item['quantity'] }}</div>
                                                                            <div class="text-sm {{ 
                                                                                $item['status'] === 'approved' ? 'text-green-600' : 
                                                                                ($item['status'] === 'rejected' ? 'text-red-600' : 'text-yellow-600') 
                                                                            }}">
                                                                                {{ $item['status'] }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>