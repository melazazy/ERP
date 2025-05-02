<!-- resources/views/livewire/inventory-reports.blade.php -->
<div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">Inventory Reports</h2>
            <div class="text-lg">
                Total Quantity: <span class="font-semibold">{{ $totalQuantity }}</span>
            </div>
        </div>

        <div class="mb-4 flex gap-4">
            <input type="text" wire:model.live="search" placeholder="Search items..." class="rounded-md">
            <select wire:model.live="category" class="rounded-md">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="subcategory" class="rounded-md">
                <option value="">All Subcategories</option>
                @foreach($subcategories as $subcat)
                    <option value="{{ $subcat->id }}">{{ $subcat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Code</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Category</th>
                        <th class="px-4 py-2">Subcategory</th>
                        <th class="px-4 py-2">Quantity</th>
                        <th class="px-4 py-2">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td class="px-4 py-2">{{ $item->code }}</td>
                        <td class="px-4 py-2">{{ $item->name }}</td>
                        <td class="px-4 py-2">{{ $item->subcategory->category->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $item->subcategory->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $item->receivings_sum_quantity ?? 0 }}</td>
                        <td class="px-4 py-2">{{ $item->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </div>
</div>