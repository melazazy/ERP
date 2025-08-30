<div>
    <h1 class="text-2xl font-bold mb-4">Items Report</h1>
    <div class="mb-4">
    <button wire:click="export" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
        Export to Excel
    </button>
</div>
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th>Item</th>
                <th>Main Department</th>
                <th>Other Departments</th>
                <th>Total Receivings</th>
                <th>Avg Receiving Price</th>
                <th>Total Receivings Value</th>
                <th>Total Requisitions</th>
                <th>Total Trusts</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = $this->getPreparedItems()->sortBy('main_department');
            @endphp
            @foreach($items as $item)
            @php
                $total_receivings = $item['total_receivings'] ?? 0;
                $avg_receiving_price = $item['avg_receiving_price'] ?? 0;
                $total_requisitions = $item['total_requisitions'] ?? 0;
                $total_trusts = $item['total_trusts'] ?? 0;
                $total_receivings_value = $item['total_receivings_value'] ?? 0;
                $balance = $item['balance'] ?? 0;
            @endphp
            <tr>
                <td>{{ $item['name'] }}</td>
                <td><strong>{{ $item['main_department'] }}</strong></td>
                <td>
                    {{ $item['other_departments'] }}
                </td>
                <td>{{ $total_receivings }}</td>
                <td>{{ number_format($avg_receiving_price, 2) }}</td>
                <td>{{ number_format($total_receivings_value, 2) }}</td>
                <td>{{ $total_requisitions }}</td>
                <td>{{ $total_trusts }}</td>
                <td>{{ $balance }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>