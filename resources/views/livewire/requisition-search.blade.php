<!-- resources/views/livewire/requisition-search.blade.php -->
<div class="container mx-auto p-4 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">


        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
                <span class="block sm:inline">{{ __('messages.' . session('message')) }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                <span class="block sm:inline">{{ __('messages.' . session('error')) }}</span>
            </div>
        @endif
        {{-- Single Requisition Search Form --}}
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">{{ __('messages.search_and_edit_single_requisition') }}</h2>
            <div class="flex gap-4 mb-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                <input type="text" wire:model="searchRequisitionNumber" wire:keydown.enter="searchRequisition"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}"
                    placeholder="{{ __('messages.enter_requisition_number') }}">
                <button wire:click="searchRequisition"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    {{ __('messages.search') }}
                </button>
            </div>

            @if(!empty($requisitionItems))
                <div class="mb-4">
                    <!-- Date and Department Update Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 {{ app()->getLocale() === 'ar' ? 'grid-cols-2-reverse' : '' }}">
                        <!-- Date -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.requested_date') }}</label>
                            <div class="flex gap-2">
                                <input type="date" wire:model="date" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}"
                                    required>
                            </div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.department') }}</label>
                            <select wire:model="selectedDepartmentId" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <option value="">{{ __('messages.select_department') }}</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
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
                                <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2">{{ __('messages.search_item') }}</label>
                                <input type="text" id="itemSearch" wire:model.live="itemSearch"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}"
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
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline self-end">
                                {{ __('messages.add_item') }}
                            </button>
                        </div>
                    </div>

                    <!-- Remove All Button -->
                    <div class="flex justify-end mb-4 {{ app()->getLocale() === 'ar' ? 'justify-start' : '' }}">
                        <button wire:click="$set('showDeleteConfirmation', true)" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            {{ __('messages.remove_all_items') }}
                        </button>
                    </div>

                    @if($showDeleteConfirmation)
                        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-xl {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <h3 class="text-lg font-semibold mb-4">{{ __('messages.confirm_deletion') }}</h3>
                                <p class="mb-4">{{ __('messages.are_you_sure_you_want_to_remove_all_items_from_this_requisition') }}</p>
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
                        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-xl {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                <h3 class="text-lg font-semibold mb-4">{{ __('messages.confirm_deletion') }}</h3>
                                <p class="mb-4">{{ __('messages.are_you_sure_you_want_to_remove_this_item_from_the_requisition') }}</p>
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
                        <table class="min-w-full bg-white border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.item') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.quantity') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.unit') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.department') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.status') }}</th>
                                    <th class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisitionItems as $item)
                                    <tr>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @if($editingItemId === $item['id'])
                                                <div>
                                                    <input type="text" 
                                                           wire:model.live="itemSearch" 
                                                           class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}"
                                                           placeholder="{{ __('messages.search_by_code_or_name') }}">
                                                    @if (!empty($searchedItems))
                                                        <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto absolute z-50 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                                            @foreach ($searchedItems as $searchedItem)
                                                                <li wire:click="selectEditingItem({{ $searchedItem['id'] }})"
                                                                    class="px-4 py-2 hover:bg-blue-100 cursor-pointer {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                                                    {{ $searchedItem['name'] }} - {{ $searchedItem['code'] }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
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
                                                       step="0.0001"
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
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            {{ $item['department']['name'] ?? 'N/A' }}
                                        </td>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            <span class="px-2 py-1 text-xs rounded-full {{ 
                                                $item['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                ($item['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                                            }}">
                                                {{ ucfirst(__('messages.' . $item['status'])) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 border {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}">
                                            @if($editingItemId === $item['id'])
                                                <div class="flex gap-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                                    <button wire:click="saveItemChanges" 
                                                            class="text-green-600 hover:text-green-800">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                    <button wire:click="cancelEdit" 
                                                            class="text-red-600 hover:text-red-800">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex gap-2 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                                                    <button wire:click="$set('showDeleteItemConfirmation', {{ $item['id'] }})" 
                                                            class="text-red-600 hover:text-red-800">
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
                </div>
            @endif
        </div>

        <hr class="my-8">

        {{-- Multiple Requisition Search Form --}}
        {{-- <div>
            <h2 class="text-xl font-bold mb-4">{{ __('messages.search_multiple_requisitions') }}</h2>
            <div class="flex gap-4 mb-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                <input type="text" wire:model="searchNumbers" wire:keydown.enter="search"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}"
                    placeholder="{{ __('messages.enter_requisition_numbers') }}">
                <button wire:click="search"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    {{ __('messages.search') }}
                </button>
            </div>
        </div> --}}
    </div>
</div>