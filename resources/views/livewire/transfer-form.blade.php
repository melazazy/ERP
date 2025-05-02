<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">Transfer Items</h1>

    <!-- Display Error/Success Messages -->
    @if (session()->has('error'))
        <div class="bg-red-500 text-white p-4 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-500 text-white p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Transfer Form -->
    <form wire:submit.prevent="save" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <!-- From Department -->
            <div>
                <label for="fromDepartmentId" class="block text-gray-700 text-sm font-bold mb-2">From Department</label>
                <select wire:model="fromDepartmentId" id="fromDepartmentId"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                    @endforeach
                </select>
                @error('fromDepartmentId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- To Department -->
            <div>
                <label for="toDepartmentId" class="block text-gray-700 text-sm font-bold mb-2">To Department</label>
                <select wire:model="toDepartmentId" id="toDepartmentId"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                    @endforeach
                </select>
                @error('toDepartmentId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Item Search -->
        <div class="mb-6">
            <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2">Search Item</label>
            <input type="text" id="itemSearch" wire:model.live="itemSearch"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Search by item name or code">
            @if (!empty($items))
                <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                    @foreach ($items as $item)
                        <li wire:click="addItem({{ $item['id'] }})"
                            class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                            {{ $item['name'] }} - {{ $item['code'] }}
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
                        <th class="py-3 px-4 border-b text-left">Item</th>
                        <th class="py-3 px-4 border-b text-left">Unit</th>
                        <th class="py-3 px-4 border-b text-left">Quantity</th>
                        <th class="py-3 px-4 border-b text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedItems as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">{{ $item['item_name'] }}</td>
                            <td class="py-2 px-4 border-b">
                                <select wire:model.live="selectedItems.{{ $index }}.unit_id" 
                                    class="shadow border rounded w-full py-1 px-2">
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                                    @endforeach
                                </select>
                                @error("selectedItems.{$index}.unit_id") 
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </td>
                            <td class="py-2 px-4 border-b">
                                <input type="number" step="0.0001"
                                    wire:model="selectedItems.{{ $index }}.quantity"
                                    class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error("selectedItems.{$index}.quantity") 
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </td>
                            <td class="py-2 px-4 border-b">
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit"
                class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500"
                {{ count($selectedItems) === 0 ? 'disabled' : '' }}>
                Transfer Items
            </button>
        </div>
    </form>
</div>