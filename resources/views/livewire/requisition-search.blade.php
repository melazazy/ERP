<!-- resources/views/livewire/requisition-search.blade.php -->
<div class="container mx-auto p-4">
    
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">


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
        {{-- Single Requisition Search Form --}}
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Search & Edit Single Requisition</h2>
            <div class="flex gap-4 mb-4">
                <input type="text" wire:model="searchRequisitionNumber" wire:keydown.enter="searchRequisition"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                    placeholder="Enter requisition number">
                <button wire:click="searchRequisition"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Search
                </button>
            </div>

            @if(!empty($requisitionItems))
                <div class="mb-4">
                    <!-- Date and Department Update Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Date -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Requested Date</label>
                            <div class="flex gap-2">
                                <input type="date" wire:model="date" 
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    required>
                            </div>
                        </div>

                        <!-- Department -->
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Department</label>
                            <select wire:model="selectedDepartmentId" 
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Update Button -->
                    <div class="flex justify-end mb-4">
                        <button wire:click="updateDateAndDepartment" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Information
                        </button>
                    </div>

                    <!-- Add New Item Section -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3">Add New Item</h3>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2">Search Item</label>
                                <input type="text" id="itemSearch" wire:model.live="itemSearch"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Search by item name or code" wire:keydown.enter.prevent="selectFirstItem">
                                @if (!empty($searchedItems))
                                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                                        @foreach ($searchedItems as $item)
                                            <li wire:click="selectItem({{ $item['id'] }})"
                                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                                {{ $item['name'] }} - {{ $item['code'] }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <button wire:click="addNewItem" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline self-end">
                                Add Item
                            </button>
                        </div>
                    </div>

                    <!-- Remove All Button -->
                    <div class="flex justify-end mb-4">
                        <button wire:click="$set('showDeleteConfirmation', true)" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Remove All Items
                        </button>
                    </div>

                    @if($showDeleteConfirmation)
                        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-xl">
                                <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
                                <p class="mb-4">Are you sure you want to remove all items from this requisition? This action cannot be undone.</p>
                                <div class="flex justify-end gap-2">
                                    <button wire:click="$set('showDeleteConfirmation', false)" 
                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Cancel
                                    </button>
                                    <button wire:click="removeAllItems; $set('showDeleteConfirmation', false)" 
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Remove All
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($showDeleteItemConfirmation)
                        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-xl">
                                <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
                                <p class="mb-4">Are you sure you want to remove this item from the requisition?</p>
                                <div class="flex justify-end gap-2">
                                    <button wire:click="$set('showDeleteItemConfirmation', null)" 
                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Cancel
                                    </button>
                                    <button wire:click="removeItem({{ $showDeleteItemConfirmation }}); $set('showDeleteItemConfirmation', null)" 
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border">Item</th>
                                    <th class="py-2 px-4 border">Quantity</th>
                                    <th class="py-2 px-4 border">Unit</th>
                                    <th class="py-2 px-4 border">Department</th>
                                    <th class="py-2 px-4 border">Status</th>
                                    <th class="py-2 px-4 border">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requisitionItems as $item)
                                    <tr>
                                        <td class="py-2 px-4 border">
                                            @if($editingItemId === $item['id'])
                                                <div>
                                                    <input type="text" 
                                                           wire:model.live="itemSearch" 
                                                           class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                           placeholder="{{ __('messages.search_by_code_or_name') }}">
                                                    @if (!empty($searchedItems))
                                                        <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto absolute z-50">
                                                            @foreach ($searchedItems as $searchedItem)
                                                                <li wire:click="selectEditingItem({{ $searchedItem['id'] }})"
                                                                    class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                                                    {{ $searchedItem['name'] }} - {{ $searchedItem['code'] }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            @else
                                                {{ $item['item']['name'] ?? 'N/A' }}
                                                @if(isset($item['item']['code']))
                                                    <div class="text-sm text-gray-500">{{ $item['item']['code'] }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border text-center">
                                            @if($editingItemId === $item['id'])
                                                <input type="number" 
                                                       wire:model="editingItem.quantity" 
                                                       step="0.0001"
                                                       class="w-24 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @else
                                                <div class="flex items-center justify-between">
                                                    <span>{{ number_format($item['quantity'] ?? 0, 2) }}</span>
                                                    <button wire:click="editItem({{ $item['id'] }})" 
                                                            class="text-blue-600 hover:text-blue-800">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border">
                                            @if($editingItemId === $item['id'])
                                                <select wire:model="editingItem.unit_id"
                                                        class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $item['unit']['name'] ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border">
                                            {{ $item['department']['name'] ?? 'N/A' }}
                                        </td>
                                        <td class="py-2 px-4 border">
                                            <span class="px-2 py-1 text-xs rounded-full {{ 
                                                $item['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                                ($item['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                                            }}">
                                                {{ ucfirst($item['status']) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 border">
                                            @if($editingItemId === $item['id'])
                                                <div class="flex gap-2">
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
                                                <div class="flex gap-2">
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
        <div>
            <h2 class="text-xl font-bold mb-4">Search Multiple Requisitions</h2>
            <div class="flex gap-4 mb-4">
                <input type="text" wire:model="searchNumbers" wire:keydown.enter="search"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                    placeholder="Enter requisition numbers (comma separated)">
                <button wire:click="search"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Search
                </button>
            </div>
        </div>
    </div>
</div>