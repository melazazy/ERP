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
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Requested Date</label>
                            <input type="date" wire:model="date" value="{{ $date }}"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
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

                    <table class="min-w-full bg-white border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 border">Item</th>
                                <th class="py-2 px-4 border">Quantity</th>
                                <th class="py-2 px-4 border">Unit</th>
                                <th class="py-2 px-4 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($requisitionItems))
                                @foreach($requisitionItems as $index => $item)
                                    <tr>
                                        <td class="py-2 px-4 border">
                                            @if($editingItemId === $item['id'])
                                                <select wire:model="editingItem.item_id"
                                                        class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @foreach($items as $itemOption)
                                                        <option value="{{ $itemOption->id }}" 
                                                                @if($editingItem['item_id'] == $itemOption->id) selected @endif>
                                                            {{ $itemOption->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
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
                                                <select wire:model="editingItem.unit_id"
                                                        class="w-full border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" 
                                                                @if($editingItem['unit_id'] == $unit->id) selected @endif>
                                                            {{ $unit->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ $item['unit']['name'] }}
                                            @endif
                                        </td>
                                        <td class="py-2 px-4 border">
                                            @if($editingItemId === $item['id'])
                                                <div class="flex gap-2">
                                                    <button wire:click="saveItemChanges" class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                                        Save
                                                    </button>
                                                    <button wire:click="cancelEdit" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                                                        Cancel
                                                    </button>
                                                </div>
                                            @else
                                                <button wire:click="editItem({{ $item['id'] }})" class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                                    Edit
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="py-2 px-4 border text-center">
                                        No requisition items found
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="mt-4 flex justify-end">
                        <button wire:click="updateRequisition"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Update Requisition
                        </button>
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