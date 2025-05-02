<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">Management Suppliers</h1>
    <form wire:submit.prevent="{{ isset($newSupplier['id']) && $newSupplier['id'] ? 'updateSupplier' : 'addSupplier' }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="Supplier Name" wire:model="newSupplier.name">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="Contact Info" wire:model="newSupplier.contact_info">
            <button type="submit" class="btn bg-blue-500 text-white rounded-lg p-2 hover:bg-blue-600">
                {{ isset($newSupplier['id']) && $newSupplier['id'] ? 'Update Supplier' : 'Add Supplier' }}
            </button>
        </div>
    </form>
    
    <div class="mb-4">
        <input type="text" class="form-input border rounded-lg p-2" placeholder="Filter by Name" wire:model.live="nameFilter">
        <input type="text" class="form-input border rounded-lg mt-2 p-2" placeholder="Filter by Contact Info" wire:model.live="contactFilter">
    </div>

    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Supplier Name</th>
                <th class="py-3 px-4 border-b text-left">Contact Info</th>
                <th class="py-3 px-4 border-b text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $supplier)
                <tr class="hover:bg-gray-50 transition duration-200">
                    <td class="py-2 px-4 border-b">{{ $supplier->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $supplier->contact_info }}</td>
                    <td class="py-2 px-4 border-b">
                        <button wire:click="edit({{ $supplier->id }})" class="bg-green-500 text-white rounded-lg px-3 py-1 hover:bg-yellow-600">Edit</button>
                        <button onclick="confirmDeleteSupplier({{ $supplier->id }})" class="bg-red-500 text-white rounded-lg px-3 py-1 hover:bg-red-600">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function confirmDeleteSupplier(supplierId) {
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
                Livewire.dispatch('deleteSupplier', { id: supplierId });
            }
        });
    }
</script>