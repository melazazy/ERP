<!-- resources/views/livewire/inventory-reports.blade.php -->
<div>
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">{{ __('messages.inventory_reports') }}</h2>
            <div class="text-lg">
                {{ __('messages.total_quantity') }}: <span class="font-semibold">{{ $totalQuantity }}</span>
            </div>
        </div>

        <div class="mb-4 flex gap-4">
            <input type="text" wire:model.live="search" placeholder="{{ __('messages.search_items') }}" class="rounded-md">
            <select wire:model.live="category" class="rounded-md">
                <option value="">{{ __('messages.all_categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="subcategory" class="rounded-md">
                <option value="">{{ __('messages.all_subcategories') }}</option>
                @foreach($subcategories as $subcat)
                    <option value="{{ $subcat->id }}">{{ $subcat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg">
                <thead>
                    <tr>
                        <th class="px-4 py-2">{{ __('messages.code') }}</th>
                        <th class="px-4 py-2">{{ __('messages.name') }}</th>
                        <th class="px-4 py-2">{{ __('messages.category') }}</th>
                        <th class="px-4 py-2">{{ __('messages.subcategory') }}</th>
                        <th class="px-4 py-2">{{ __('messages.quantity') }}</th>
                        <th class="px-4 py-2">{{ __('messages.unit') }}</th>
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