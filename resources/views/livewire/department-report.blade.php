<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-6">{{ __('messages.department_report.title') }}</h2>
            
            <!-- Filters -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.department_report.filters.department') }}</label>
                    <select wire:model.live="selectedDepartment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('messages.department_report.filters.select_department') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.department_report.filters.document_type') }}</label>
                    <select wire:model="selectedDocType" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="all">{{ __('messages.department_report.filters.all_documents') }}</option>
                        <option value="receiving">{{ __('messages.department_report.filters.receivings_only') }}</option>
                        <option value="requisition">{{ __('messages.department_report.filters.requisitions_only') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.department_report.filters.from_date') }}</label>
                    <input type="date" wire:model="dateFrom" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.department_report.filters.to_date') }}</label>
                    <input type="date" wire:model="dateTo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">{{ __('messages.department_report.filters.doc_number') }}</label>
                <input type="text" wire:model.live="docNumber" placeholder="{{ __('messages.department_report.filters.search_doc') }}" class="mt-1 block w-64 rounded-md border-gray-300 shadow-sm">
            </div>

            <div wire:loading wire:target="generateReport, selectedDepartment, dateFrom, dateTo, selectedDocType, docNumber" class="mb-4">
                <div class="animate-pulse flex space-x-4 items-center">
                    <div class="h-4 w-4 bg-blue-400 rounded-full"></div>
                    <div class="text-gray-600">{{ __('messages.department_report.loading') }}</div>
                </div>
            </div>

            <button wire:click="generateReport" class="mb-6 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                {{ __('messages.department_report.generate_report') }}
            </button>

            @if($selectedDepartment)
                @if(($selectedDocType === 'all' || $selectedDocType === 'receiving') && count($receivings) > 0)
                    <!-- Receivings Table -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4">{{ __('messages.department_report.receivings.title') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.receivings.date') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.receivings.doc_number') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.receivings.item') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.receivings.supplier') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.receivings.quantity') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.receivings.unit_price') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.receivings.total') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($receivings as $receiving)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $receiving->received_at ? $receiving->received_at->format('d/m/Y') : '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('transaction.details', ['type' => 'receiving', 'number' => $receiving->receiving_number]) }}" 
                                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $receiving->receiving_number ?? '-' }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $receiving->item->name ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $receiving->supplier->name ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $receiving->quantity ?? 0 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format($receiving->unit_price ?? 0, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ number_format(($receiving->quantity ?? 0) * ($receiving->unit_price ?? 0), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">{{ __('messages.department_report.receivings.no_data') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($receivings) > 0)
                                    <tfoot>
                                        <tr class="bg-gray-50">
                                            <td colspan="4" class="px-6 py-3 text-right font-semibold">{{ __('messages.department_report.receivings.total') }}:</td>
                                            <td class="px-6 py-3 font-semibold">{{ $totalReceivings }}</td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif

                @if(($selectedDocType === 'all' || $selectedDocType === 'requisition') && count($requisitions) > 0)
                    <!-- Requisitions Table -->
                    <div>
                        <h3 class="text-xl font-semibold mb-4">{{ __('messages.department_report.requisitions.title') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.requisitions.date') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.requisitions.doc_number') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.requisitions.item') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.requisitions.requested_by') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.requisitions.quantity') }}</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.department_report.requisitions.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($requisitions as $requisition)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $requisition->requested_date ? $requisition->requested_date->format('d/m/Y') : '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('transaction.details', ['type' => 'requisition', 'number' => $requisition->requisition_number]) }}" 
                                                   class="text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ $requisition->requisition_number ?? '-' }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $requisition->item->name ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $requisition->requester->name ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $requisition->quantity ?? 0 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ ($requisition->status ?? '') === 'approved' ? 'bg-green-100 text-green-800' : 
                                                       (($requisition->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($requisition->status ?? 'unknown') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">{{ __('messages.department_report.requisitions.no_data') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($requisitions) > 0)
                                    <tfoot>
                                        <tr class="bg-gray-50">
                                            <td colspan="4" class="px-6 py-3 text-right font-semibold">{{ __('messages.department_report.requisitions.total') }}:</td>
                                            <td class="px-6 py-3 font-semibold">{{ $totalRequisitions }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                @endif

                @if(($selectedDocType === 'all' && count($receivings) === 0 && count($requisitions) === 0) || 
                    ($selectedDocType === 'receiving' && count($receivings) === 0) || 
                    ($selectedDocType === 'requisition' && count($requisitions) === 0))
                    <div class="text-center text-gray-500 py-8">
                        {{ __('messages.department_report.no_data_period') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>