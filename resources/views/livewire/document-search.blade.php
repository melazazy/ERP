<div class="space-y-4">
    <!-- Document Type Toggle -->
    <div class="flex justify-between items-center">
        <div class="flex space-x-2">
            <label class="inline-flex items-center">
                <input type="radio" name="documentType" wire:model="documentType" value="receiving" class="form-radio">
                <span class="ml-2">Receiving</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="documentType" wire:model="documentType" value="requisition" class="form-radio">
                <span class="ml-2">Requisition</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="documentType" wire:model="documentType" value="trust" class="form-radio">
                <span class="ml-2">Trust</span>
            </label>
        </div>
    </div>

    <!-- Date Range Filters -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">From Date</label>
            <input type="date" wire:model="fromDate"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">To Date</label>
            <input type="date" wire:model="toDate"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    </div>

    <!-- Search Button -->
    <div class="flex justify-end">
        <button wire:click="submitSearch" 
            class="px-4 py-2 text-sm font-medium bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Search
        </button>
    </div>

    <!-- Documents List -->
    @if(!empty($documents))
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">department</th> --}}
                    @if($documentType === 'receiving')
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($documents as $document)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            @php
                                $docNumber = $document['receiving_number'] ?? $document['requisition_number'] ?? $document['trust_number'] ?? $document['transfer_number'];
                                $docType = $document['document_type'];
                            @endphp
                            <a href="{{ route('transaction.details', ['type' => $docType, 'number' => $docNumber]) }}" 
                               class="text-blue-600 hover:text-blue-800 hover:underline">
                                {{ $docNumber }}
                            </a>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                            <div class="text-sm text-gray-900">
                                {{ $document['document_type'] }}
                            </div>
                          
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            @if (isset($document['received_at']))
                                {{ Carbon\Carbon::parse($document['received_at'])->format('d-m-Y') }}
                            @elseif (isset($document['requested_date']))
                                {{ Carbon\Carbon::parse($document['requested_date'])->format('d-m-Y') }}
                            @elseif (isset($document['trust_date']))
                                {{ Carbon\Carbon::parse($document['trust_date'])->format('d-m-Y') }}
                            @else
                                {{ Carbon\Carbon::parse($document['transfer_date'])->format('d-m-Y') }}
                            @endif
                        </div>
                    </td>
                    {{-- <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-gray-900">
                            {{dd($document) }}
                        </div>
                    </td> --}}
                    @if($documentType === 'receiving')
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-gray-900">
                            ${{ number_format($document['subtotal'] ?? 0, 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-gray-900">
                            ${{ number_format($document['tax_amount'] ?? 0, 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-gray-900">
                            ${{ number_format($document['discount_amount'] ?? 0, 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-medium text-gray-900">
                            ${{ number_format($document['total_amount'] ?? 0, 2) }}
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td class="px-6 py-4 text-center text-gray-500" colspan="{{ $documentType === 'receiving' ? 8 : 3 }}">
                        No documents found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>