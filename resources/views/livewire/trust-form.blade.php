<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">{{ __('messages.add_trust') }}</h1>

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

    <!-- Form for Adding Trust -->
    <form wire:submit.prevent="addTrust" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <!-- Trust Number -->
            <div>
                <label for="requisitionNumber" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.requisition_number') }}</label>
                <input type="text" id="requisitionNumber" wire:model="requisitionNumber"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="{{ __('messages.requisition_number_placeholder') }}">
            </div>

            <!-- Department Search -->
            <div>
                <label for="departmentSearch" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.department') }}</label>
                <input type="text" wire:model.live="departmentSearch" id="departmentSearch"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="{{ __('messages.search_department') }}">
                @if (!empty($departments))
                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                        @foreach ($departments as $department)
                            <li wire:click="selectDepartment({{ $department['id'] }})"
                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                {{ $department['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Requested By Search -->
            <div>
                <label for="requestedBySearch" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.requested_by') }}</label>
                <input type="text" wire:model.live="requestedBySearch" id="requestedBySearch"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="{{ __('messages.search_user') }}">
                @if (!empty($users))
                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                        @foreach ($users as $user)
                            <li wire:click="selectUser({{ $user['id'] }})"
                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                                {{ $user['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            <!-- Requested Date -->
            <div>
                <label for="requestedDate" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.requested_date') }}</label>
                <input type="date" id="requestedDate" wire:model.live="date"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Item Search -->
        <div class="mb-6">
            <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                {{ __('messages.search_item') }}
            </label>
            <div class="relative">
                <input type="text" id="itemSearch" wire:model.live.debounce.300ms="itemSearch"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="{{ __('messages.search_by_item_name_or_code') }}"
                    wire:keydown.enter.prevent="$dispatch('item-selected')">
                
                @if(!empty($itemSearch))
                    <button wire:click="$set('itemSearch', '')" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </button>
                @endif
            </div>
            
            @if (!empty($items))
                <div class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-60 overflow-y-auto">
                    @forelse ($items as $item)
                        <div wire:click="selectItem({{ $item['id'] }})"
                            class="px-4 py-2 hover:bg-blue-50 cursor-pointer flex justify-between items-center border-b border-gray-100 last:border-0 {{ $item['available_quantity'] <= 0 ? 'opacity-60' : '' }}">
                            <div class="flex items-center">
                                <span class="font-medium">{{ $item['name'] }}</span>
                                <span class="text-gray-500 text-sm mr-2">({{ $item['code'] }})</span>
                                @if($item['available_quantity'] <= 0)
                                    <span class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded-full">
                                        {{ __('messages.out_of_stock') }}
                                    </span>
                                @endif
                            </div>
                            @if($item['available_quantity'] > 0)
                                <div class="text-sm text-gray-600">
                                    {{ $item['available_quantity'] }} {{ __('messages.available') }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="px-4 py-2 text-gray-500 text-center">
                            {{ __('messages.no_items_found') }}
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <!-- Selected Items Table -->
        <div class="mb-6">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.item') }}</th>
                        <th class="py-3 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.code') }}</th>
                        <th class="py-3 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.quantity') }}</th>
                        <th class="py-3 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.possible_amount') }}</th>
                        <th class="py-3 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($selectedItems as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ $item['name'] }}</td>
                            <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ $item['code'] }}</td>
                            <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                <input type="number" wire:model="selectedItems.{{ $index }}.quantity"
                                    class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </td>
                            <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                <span>{{ $item['possible_amount'] }}</span> <!-- Display possible amount -->
                            </td>
                            <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                <button type="button" wire:click="removeSelectedItem({{ $index }})"
                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    {{ __('messages.remove') }}
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
                {{ __('messages.save_trust') }}
            </button>
        </div>
    </form>
</div>
<script>
    function confirmDeleteTrust(trustId) {
        Swal.fire({
            title: '{{ __('messages.are_you_sure') }}',
            text: "{{ __('messages.cannot_revert') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '{{ __('messages.yes_delete') }}'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteTrust', {
                    id: trustId
                });
            }
        });
    }
</script>