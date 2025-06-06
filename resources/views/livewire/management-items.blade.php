<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">Management Items</h1>
    <form wire:submit.prevent="{{ isset($newItem['id']) && $newItem['id'] ? 'updateItem' : 'addItem' }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="Item Name" wire:model="newItem.name">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="Item Code" wire:model="newItem.code">
            <select class="form-input border rounded-lg p-2" wire:model.live="selectedCategory">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select class="form-input border rounded-lg p-2" wire:model="newItem.subcategory">
                <option value="">Select Subcategory</option>
                @foreach($filteredSubcategories as $subcategory)
                    <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                @endforeach
            </select>
            {{-- <select class="form-input border rounded-lg p-2" wire:model="newItem.department">
                <option value="">Select Department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select> --}}
            <button type="submit" class="btn bg-blue-500 text-white rounded-lg p-2 hover:bg-blue-600">
                {{ isset($newItem['id']) && $newItem['id'] ? 'Update Item' : 'Add Item' }}
            </button>
        </div>
    </form>
    <div class="mb-4">
        <input type="text" class="form-input border rounded-lg p-2" placeholder="Filter by Category" wire:model.live="categoryFilter">
        <input type="text" class="form-input border rounded-lg mt-2 p-2" placeholder="Filter by Subcategory" wire:model.live="subcategoryFilter">
        <input type="text" class="form-input border rounded-lg mt-2 p-2" placeholder="Filter by Name or Code" wire:model.live="itemFilter">
    </div>
    <div class="mb-4">
        <button wire:click="exportItems" class="bg-blue-500 text-white rounded-lg px-4 py-2 hover:bg-blue-600">
            Download Items as CSV
        </button>
    </div>
    @if (session()->has('message'))
    <div class="mt-4 p-4 bg-green-100 text-green-700 rounded">
        {{ session('message') }}
    </div>
@endif
    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Item Name</th>
                <th class="py-3 px-4 border-b text-left">Item Code</th>
                <th class="py-3 px-4 border-b text-left">Category</th>
                <th class="py-3 px-4 border-b text-left">Subcategory</th>
                <th class="py-3 px-4 border-b text-left">Possible Amount</th> <!-- New column -->
                <th class="py-3 px-4 border-b text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr class="hover:bg-gray-50 transition duration-200">
                    <td class="py-2 px-4 border-b">{{ $item->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $item->code }}</td>
                    <td class="py-2 px-4 border-b">{{ $item->subcategory->category->name ?? 'N/A' }}</td>
                    <td class="py-2 px-4 border-b">{{ $item->subcategory->name ?? 'N/A' }}</td>
                    <td class="py-2 px-4 border-b">{{ $item->possible_amount }}</td>
                    <td class="py-2 px-4 border-b">
                        <button wire:click="edit({{ $item->id }})" class="bg-green-500 text-white rounded-lg px-3 py-1 hover:bg-yellow-600">Edit</button>
                        {{-- <button onclick="confirmDelete({{ $item->id }})" class="bg-red-500 text-white rounded-lg px-3 py-1 hover:bg-red-600">Delete</button> --}}

                    {{-- <button wire:click="delete({{ $item->id }})" class="bg-red-500 text-white rounded-lg px-3 py-1 hover:bg-red-600">Delete</button> --}}
                    <button onclick="confirmDelete({{ $item->id }})" class="bg-red-500 text-white rounded-lg px-3 py-1 hover:bg-red-600">Delete</button>
                </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
    function confirmDelete(itemId) {
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
                Livewire.dispatch('deleteItem', { id: itemId });
            }
        });
    }
</script>
