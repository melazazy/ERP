<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">Management Departments</h1>
    <form wire:submit.prevent="{{ isset($newDepartment['id']) && $newDepartment['id'] ? 'updateDepartment' : 'addDepartment' }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="Department Name" wire:model="newDepartment.name">
            <button type="submit" class="btn bg-blue-500 text-white rounded-lg p-2 hover:bg-blue-600">
                {{ isset($newDepartment['id']) && $newDepartment['id'] ? 'Update Department' : 'Add Department' }}
            </button>
        </div>
    </form>
    
    <div class="mb-4">
        <input type="text" class="form-input border rounded-lg p-2" placeholder="Filter by Name" wire:model.live="nameFilter">
    </div>

    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Department Name</th>
                <th class="py-3 px-4 border-b text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $department)
                <tr class="hover:bg-gray-50 transition duration-200">
                    <td class="py-2 px-4 border-b">{{ $department->name }}</td>
                    <td class="py-2 px-4 border-b">
                        <button wire:click="edit({{ $department->id }})" class="bg-green-500 text-white rounded-lg px-3 py-1 hover:bg-yellow-600">Edit</button>
                        <button onclick="confirmDeleteDepartment({{ $department->id }})" class="bg-red-500 text-white rounded-lg px-3 py-1 hover:bg-red-600">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function confirmDeleteDepartment(departmentId) {
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
                Livewire.dispatch('deleteDepartment', { id: departmentId });
            }
        });
    }
</script>
