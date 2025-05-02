<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">Management Subcategories</h1>
    <form wire:submit.prevent="{{ isset($newSubcategory['id']) && $newSubcategory['id'] ? 'updateSubcategory' : 'addSubcategory' }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="Subcategory Name" wire:model="newSubcategory.name">
            <input type="text" class="form-input border rounded-lg p-2" placeholder="Description" wire:model="newSubcategory.description">
            <select class="form-input border rounded-lg p-2" wire:model="newSubcategory.category_id">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn bg-blue-500 text-white rounded-lg p-2 hover:bg-blue-600">
                {{ isset($newSubcategory['id']) && $newSubcategory['id'] ? 'Update Subcategory' : 'Add Subcategory' }}
            </button>
        </div>
    </form>

    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-lg">
        <thead class="bg-gray-200">
            <tr>
                <th class="py-3 px-4 border-b text-left">Subcategory Name</th>
                <th class="py-3 px-4 border-b text-left">Description</th>
                <th class="py-3 px-4 border-b text-left">Category</th>
                <th class="py-3 px-4 border-b text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($subcategories as $subcategory)
                <tr>
                    <td class="py-2 px-4 border-b">{{ $subcategory->name }}</td>
                    <td class="py-2 px-4 border-b">{{ $subcategory->description }}</td>
                    <td class="py-2 px-4 border-b">{{ $subcategory->category->name }}</td>
                    <td class="py-2 px-4 border-b">
                        <button wire:click="editSubcategory({{ $subcategory->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                        <button wire:click="deleteSubcategory({{ $subcategory->id }})" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>