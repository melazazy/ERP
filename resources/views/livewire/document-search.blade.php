<!-- Document Search Page -->
<div class="py-12 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl sm:rounded-lg p-6">
            <!-- Title -->
            <h2 class="text-2xl font-semibold mb-6">{{ __('messages.document_search') }}</h2>

            <!-- Search and Filter Section -->
            <div class="mb-6 space-y-4">
                <!-- Document Type Toggle -->
                <div class="flex flex-col space-y-2">
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.document_type') }}</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="documentType" wire:model="documentType" value="receiving" class="form-radio h-4 w-4 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.receiving') }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="documentType" wire:model="documentType" value="requisition" class="form-radio h-4 w-4 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.requisition') }}</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="documentType" wire:model="documentType" value="trust" class="form-radio h-4 w-4 text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.trust') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Date Range Filters -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.from_date') }}</label>
                        <input type="date" wire:model="fromDate"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.to_date') }}</label>
                        <input type="date" wire:model="toDate"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex justify-end">
                    <button wire:click="submitSearch" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('messages.search') }}
                    </button>
                </div>
            </div>

            <!-- Documents List -->
            @if(!empty($documents))
            <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.document_number') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.type') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date') }}</th>
                            @if($documentType === 'receiving')
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.subtotal') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.tax') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.discount') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.total') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $docNumber = $document['receiving_number'] ?? $document['requisition_number'] ?? $document['trust_number'] ?? $document['transfer_number'];
                                    $docType = $document['document_type'];
                                @endphp
                                <a href="{{ route('transaction.details', ['type' => $docType, 'number' => $docNumber]) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 hover:underline font-medium">
                                    {{ $docNumber }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $document['document_type'] }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">
                                    @if (isset($document['received_at']))
                                        {{ Carbon\Carbon::parse($document['received_at'])->format('d-m-Y') }}
                                    @elseif (isset($document['requested_date']))
                                        {{ Carbon\Carbon::parse($document['requested_date'])->format('d-m-Y') }}
                                    @elseif (isset($document['trust_date']))
                                        {{ Carbon\Carbon::parse($document['trust_date'])->format('d-m-Y') }}
                                    @else
                                        {{ Carbon\Carbon::parse($document['transfer_date'])->format('d-m-Y') }}
                                    @endif
                                </span>
                            </td>
                            @if($documentType === 'receiving')
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-900">${{ number_format($document['subtotal'] ?? 0, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-900">${{ number_format($document['tax_amount'] ?? 0, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-900">${{ number_format($document['discount_amount'] ?? 0, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-medium text-gray-900">${{ number_format($document['total_amount'] ?? 0, 2) }}</span>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $documentType === 'receiving' ? 7 : 3 }}" class="px-6 py-4 text-center text-gray-500">
                                {{ __('messages.no_documents_found') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>