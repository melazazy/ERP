<div class="container mx-auto mt-5">
    <h1 class="text-3xl font-bold mb-6 text-center">{{ __('messages.add_requisitions') }}</h1>

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

    <!-- Form for Adding Requisitions -->
    <form wire:submit.prevent="addRequisition" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <!-- Requisition Number -->
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
                    <!-- Show the list only if $departments is not empty -->
                    <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                        @foreach ($departments as $department)
                            <li wire:click="selectDepartment({{ $department['id'] }})"
                                class="px-4 py-2 hover:bg-blue-100 cursor-pointer" dir="rtl">
                                {{ $department['name'] }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Requested By Search -->
            <div>
                <label for="requestedBySearch" class="block text-gray-700 text-sm mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.optional_requested_by') }}</label>
                <input type="text" wire:model.live="requestedBySearch" id="requestedBySearch"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="{{ __('messages.search_user_optional') }}">
                @if (!empty($users))
                    <!-- Show the list only if $users is not empty -->
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
          
            {{-- Date --}}
            <div class="flex-1 min-w-[200px]">
                <label for="date" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.date') }}</label>
                <input type="date" id="date" wire:model="date"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Item Search -->
        <div class="mb-6">
            <label for="itemSearch" class="block text-gray-700 text-sm font-bold mb-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.search_item') }}</label>
            <input type="text" id="itemSearch" wire:model.live="itemSearch"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="{{ __('messages.search_by_item_name_or_code') }}" wire:keydown.enter.prevent="selectFirstItem">
            @if (!empty($items))
                <!-- Show the list only if $items is not empty -->
                <ul class="mt-2 bg-white border border-gray-300 rounded-lg shadow-md max-h-40 overflow-y-auto">
                    @foreach ($items as $item)
                        <li wire:click="selectItem({{ $item['id'] }})"
                            class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                            {{ $item['name'] }} - {{ $item['code'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Selected Items Table -->
        <div class="mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-3 px-4 border text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ __('messages.id') }}</th>
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
                                <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">{{ $index + 1 }}</td>
                                <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}" dir="rtl">
                                    <div class="flex items-center">
                                        <span class="block md:hidden">{{ __('messages.item') }}:</span>
                                        {{ $item['name'] }}
                                    </div>
                                </td>
                                <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <div class="flex items-center">
                                        <span class="block md:hidden">{{ __('messages.code') }}:</span>
                                        {{ $item['code'] }}
                                    </div>
                                </td>
                                <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <div class="flex items-center">
                                        <span class="block md:hidden">{{ __('messages.qty') }}:</span>
                                        <input type="number" step="0.0001" wire:model="selectedItems.{{ $index }}.quantity"
                                            class="w-20 text-center border rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                </td>
                                <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <div class="flex items-center">
                                        <span class="block md:hidden">{{ __('messages.possible') }}:</span>
                                        <span>{{ $item['possible_amount'] }}</span>
                                    </div>
                                </td>
                                <td class="py-2 px-4 border-b text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                                    <div class="flex items-center">
                                        <button type="button" wire:click="removeSelectedItem({{ $index }})"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                            <span class="block md:hidden">{{ __('messages.remove') }}</span>
                                            <span class="hidden md:block">{{ __('messages.remove_item') }}</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button type="submit"
                class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                {{ __('messages.save_requisition') }}
            </button>
        </div>
    </form>
</div>
<script>
    function confirmDeleteRequisition(requisitionId) {
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
                Livewire.dispatch('deleteRequisition', {
                    id: requisitionId
                });
            }
        });
    }
</script>
