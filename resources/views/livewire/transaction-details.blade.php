<div class="container mx-auto p-4">
    <style>
        @media print {
            /* Hide everything except the transaction details */
            body * {
                visibility: hidden;
            }
            
            .print-section, .print-section * {
                visibility: visible;
            }
            
            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            /* Hide non-printable elements */
            .no-print, .no-print * {
                display: none !important;
            }
            
            /* Optimize table for printing */
            .print-table th, .print-table td {
                padding: 4px 8px !important;
                font-size: 11px !important;
            }
            
            .print-table th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            /* Ensure page breaks don't happen in the middle of rows */
            .print-table tr {
                page-break-inside: avoid;
            }
            
            /* Add page margins */
            @page {
                margin: 0.5cm;
                size: portrait;
            }
            
            /* Remove shadows and borders for better printing */
            .print-section .shadow-md,
            .print-section .shadow-sm {
                box-shadow: none !important;
            }
            
            /* Ensure background colors print */
            .print-section .bg-gradient-to-r,
            .print-section .bg-blue-50,
            .print-section .bg-gray-50 {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @if(!empty($items))
            <div class="mb-8 print-section">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-t-lg border-b border-blue-200 shadow-sm">
                    <h3 class="text-xl font-bold text-gray-800">{{ ucfirst($transactionType) }} #{{ $transactionNumber }}</h3>
                    <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>
                                @if($transactionType === 'receiving')
                                    Received Date: 
                                @elseif($transactionType === 'requisition')
                                    Requested Date: 
                                @else
                                    Trust Date: 
                                @endif
                                {{ date('Y-m-d', strtotime($transactionData['date'])) }}
                            </span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <span>Department: {{ $transactionData['department']['name'] ?? 'N/A' }}</span>
                        </div>
                        
                        @if($transactionType === 'receiving')
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <span>Supplier: {{ $transactionData['supplier']['name'] ?? 'N/A' }}</span>
                            </div>
                        @endif
                        
                        @if($transactionType === 'trust')
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Employee: {{ $transactionData['employee']['name'] ?? 'N/A' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="overflow-x-auto bg-white rounded-b-lg shadow-md">
                    <table class="min-w-full divide-y divide-gray-200 print-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                
                                @if($transactionType === 'receiving')
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                @endif
                                
                                @if($transactionType === 'requisition')
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                @endif
                                
                                @if($transactionType === 'trust')
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['item']['name'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $item['item']['code'] ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">{{ number_format($item['quantity'] ?? 0, 2) }}</td>
                                    
                                    @if($transactionType === 'receiving')
                                    @php
                                        $itemSubtotal = $item['quantity'] * $item['unit_price'];
                                        $itemTax = $itemSubtotal * ($item['tax'] / 100);
                                        $itemDiscount = $itemSubtotal * ($item['discount'] / 100);
                                        $itemTotal = $itemSubtotal + $itemTax - $itemDiscount;
                                    @endphp
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['unit']['name'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">{{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">{{ number_format($itemSubtotal, 2) }}</td>
                                    @endif
                                    
                                    @if($transactionType === 'requisition')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item['unit']['name'] ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full {{ 
                                            $item['status'] === 'approved' ? 'bg-green-100 text-green-800' : 
                                            ($item['status'] === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                                        }}">
                                            {{ ucfirst($item['status'] ?? 'pending') }}
                                        </span>
                                    </td>
                                    @endif
                                    
                                    @if($transactionType === 'trust')
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full {{ 
                                            $item['status'] === 'returned' ? 'bg-green-100 text-green-800' : 
                                            'bg-yellow-100 text-yellow-800'
                                        }}">
                                            {{ ucfirst($item['status'] ?? 'active') }}
                                        </span>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                            
                            @if($transactionType === 'receiving')
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($totalSubtotal, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Tax:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($totalTax, 2) }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Discount:</td>
                                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">{{ number_format($totalDiscount, 2) }}</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <td colspan="5" class="px-6 py-3 text-right text-sm font-bold text-gray-700">Grand Total:</td>
                                    <td class="px-6 py-3 text-right text-sm font-bold text-blue-700">{{ number_format($grandTotal, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-6 flex justify-between no-print">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700  text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </button>
            </div>
        @else
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mt-4" role="alert">
                <span class="block sm:inline">No transaction data found.</span>
            </div>
        @endif
    </div>
</div>