<!-- resources/views/livewire/trust-search.blade.php -->
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

        {{-- Single Trust Search Form --}}
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4">Search & Edit Single Trust</h2>
            <div class="flex gap-4 mb-4">
                <input type="text" wire:model="searchTrustNumber" wire:keydown.enter="searchTrust"
                    class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-1"
                    placeholder="Enter trust number">
                <button wire:click="searchTrust"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Search
                </button>
            </div>

            @if(!empty($trustItems))
                <div class="mb-4">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Requested Date</label>
                            <input type="date" wire:model="date" value="{{ $date }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Department</label>
                            <select wire:model="selectedDepartmentId" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <table class="min-w-full bg-white border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 border">Item</th>
                                <th class="py-2 px-4 border">Quantity</th>
                                <th class="py-2 px-4 border">Department</th>
                                <th class="py-2 px-4 border">Requested By</th>
                                <th class="py-2 px-4 border">Status</th>
                                <th class="py-2 px-4 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trustItems as $item)
                                <tr>
                                    <td class="py-2 px-4 border">
                                        @if($editingItemId === $item['id'])
                                            <div class="relative">
                                                <div class="flex items-center mb-2">
                                                    <input type="text" wire:model.live="itemSearch" 
                                                        class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                        placeholder="Search item by code or name...">
                                                    @if(!empty($editingItem['item_name']))
                                                        <div class="ml-2 text-sm text-gray-500">
                                                            {{ $editingItem['item_code'] }}
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                @if(!empty($searchedItems))
                                                    <div class="absolute z-10 w-full bg-white border rounded shadow-lg max-h-60 overflow-auto">
                                                        @foreach($searchedItems as $searchedItem)
                                                            <div wire:click="selectItem({{ $searchedItem['id'] }})"
                                                                class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b last:border-0">
                                                                <div class="flex items-center">
                                                                    <div class="flex-1">
                                                                        <div class="font-medium">{{ $searchedItem['name'] }}</div>
                                                                        <div class="text-sm text-gray-500">{{ $searchedItem['code'] }}</div>
                                                                    </div>
                                                                    <div class="text-sm text-gray-500">
                                                                        {{ $searchedItem['subcategory']['name'] }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            {{ $item['item']['name'] }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border">
                                        @if($editingItemId === $item['id'])
                                            <input type="number" step="0.0001" wire:model="editingItem.quantity"
                                                class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @else
                                            {{ $item['quantity'] }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border">
                                        @if($editingItemId === $item['id'])
                                            <select wire:model="editingItem.department_id"
                                                class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Select Department</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department['id'] }}"
                                                            @if($editingItem['department_id'] == $department['id']) selected @endif>
                                                        {{ $department['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{ $item['department']['name'] }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border">
                                        @if($editingItemId === $item['id'])
                                            <select wire:model="editingItem.requested_by_id"
                                                class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Select User</option>
                                                @foreach($users as $user)
                                                    <option value="{{ $user['id'] }}"
                                                            @if($editingItem['requested_by_id'] == $user['id']) selected @endif>
                                                        {{ $user['name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            {{ $item['requested_by']['name'] }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border">
                                        @if($editingItemId === $item['id'])
                                            <select wire:model="editingItem.status"
                                                class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Select Status</option>
                                                <option value="pending" @if($editingItem['status'] == 'pending') selected @endif>Pending</option>
                                                <option value="approved" @if($editingItem['status'] == 'approved') selected @endif>Approved</option>
                                                <option value="rejected" @if($editingItem['status'] == 'rejected') selected @endif>Rejected</option>
                                            </select>
                                        @else
                                            {{ $item['status'] }}
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 border">
                                        @if($editingItemId === $item['id'])
                                            <button wire:click="saveItemChanges" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Save</button>
                                            <button wire:click="cancelEdit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cancel</button>
                                        @else
                                            <button wire:click="editItem({{ $item['id'] }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Edit</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>