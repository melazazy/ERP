<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">Add Requisitions</h1>

    <!-- Form for Adding Requisitions -->
    <form wire:submit.prevent="addRequisition" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            {{-- add requsition number --}}
            <div class="mb-6">
                <label for="requisitionNumber" class="block text-gray-700 text-sm font-bold mb-2">Requisition Number</label>
                <input type="text" id="requisitionNumber" wire:model="requisitionNumber"
                       class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="requisition number">
            </div>
            <!-- Department Search -->
            <div>
                <label for="departmentSearch" class="block text-gray-700 text-sm font-bold mb-2">Department</label>
                <input type="text" wire:model.live="departmentSearch" id="departmentSearch"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Department">
                @if(!empty($departments)) <!-- Show the list only if $departments is not empty -->
                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                        @foreach($departments as $department)
                            <li wire:click="selectDepartment({{ $department['id'] }})"
                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                {{ $department['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <!-- Item Search -->
            <div class="mb-6">
                <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2">Search Item</label>
                <input type="text" id="itemSearch" wire:model.live="itemSearch"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Search by item name or code">
                <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                    @foreach($items as $item)
                        <li wire:click="selectItem({{ $item['id'] }})"
                            class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                            {{ $item['name'] }} - {{ $item['code'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>


        <!-- Selected Items Table -->
        <div class="mb-6">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4 border-b text-left">Item</th>
                        <th class="py-3 px-4 border-b text-left">Code</th>
                        <th class="py-3 px-4 border-b text-left">Quantity</th>
                        <th class="py-3 px-4 border-b text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedItems as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b">{{ $item['name'] }}</td>
                            <td class="py-2 px-4 border-b">{{ $item['code'] }}</td>
                            <td class="py-2 px-4 border-b">
                                <input type="number" wire:model="selectedItems.{{ $index }}.quantity"
                                       class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="py-2 px-4 border-b">
                                <button type="button" wire:click="removeSelectedItem({{ $index }})"
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
                    class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                Save Requisition
            </button>
        </div>
    </form>

    <!-- Filters for Requisitions Table -->
    <div class="mb-4">
        <input type="text" class="form-input border rounded-lg p-2" placeholder="Filter by Item" wire:model.live="itemFilter">
        <input type="text" class="form-input border rounded-lg mt-2 p-2" placeholder="Filter by Department" wire:model.live="departmentFilter">
        <select class="form-input border rounded-lg mt-2 p-2" wire:model.live="statusFilter">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    <!-- Requisitions Table -->
    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Item</th>
                <th class="py-3 px-4 border-b text-left">Department</th>
                <th class="py-3 px-4 border-b text-left">Quantity</th>
                <th class="py-3 px-4 border-b text-left">Requested By</th>
                <th class="py-3 px-4 border-b text-left">Status</th>
                <th class="py-3 px-4 border-b text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisitions as $requisition)
                <tr class="hover:bg-gray-50 transition duration-200">
                    <td class="py-2 px-4 border-b">{{ $requisition->item->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->department->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->quantity }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->requester->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $requisition->status }}</td>
                    <td class="py-2 px-4 border-b">
                        <button wire:click="edit({{ $requisition->id }})" class="bg-green-500 text-white rounded-lg px-3 py-1 hover:bg-yellow-600">Edit</button>
                        <button onclick="confirmDeleteRequisition({{ $requisition->id }})" class="bg-red-500 text-white rounded-lg px-3 py-1 hover:bg-red-600">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
    function confirmDeleteRequisition(requisitionId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteRequisition', { id: requisitionId });
            }
        });
    }
</script>
