<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">Management Users</h1>
    <form wire:submit.prevent="{{ isset($newUser['id']) && $newUser['id'] ? 'updateUser' : 'addUser' }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="User Name" wire:model="newUser.name">
            <input type="email" class="form-input border rounded-lg p-2" placeholder="Email" wire:model="newUser.email">
            <select class="form-input border rounded-lg p-2" wire:model="newUser.department_id">
                <option value="">Select Department</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn bg-blue-500 text-white rounded-lg p-2 hover:bg-blue-600">
                {{ isset($newUser['id']) && $newUser['id'] ? 'Update User' : 'Add User' }}
            </button>
        </div>
    </form>
    
    <div class="mb-4">
        <input type="text" class="form-input border rounded-lg p-2" placeholder="Filter by Name" wire:model.live="nameFilter">
        <input type="text" class="form-input border rounded-lg mt-2 p-2" placeholder="Filter by Email" wire:model.live="emailFilter">
    </div>

    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">User Name</th>
                <th class="py-3 px-4 border-b text-left">Email</th>
                <th class="py-3 px-4 border-b text-left">Department</th>
                <th class="py-3 px-4 border-b text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr class="hover:bg-gray-50 transition duration-200">
                    <td class="py-2 px-4 border-b">{{ $user->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $user->email }}</td>
                    <td class="py-2 px-4 border-b">{{ $user->department->name ?? 'N/A' }}</td>
                    <td class="py-2 px-4 border-b">
                        <button wire:click="edit({{ $user->id }})" class="bg-green-500 text-white rounded-lg px-3 py-1 hover:bg-yellow-600">Edit</button>
                        <button onclick="confirmDeleteUser({{ $user->id }})" class="bg-red-500 text-white rounded-lg px-3 py-1 hover:bg-red-600">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function confirmDeleteUser(userId) {
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
                Livewire.dispatch('deleteUser', { id: userId });
            }
        });
    }
</script>