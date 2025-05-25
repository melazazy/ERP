<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Export' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .date {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }
        td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            font-size: 10px;
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .status {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="date">{{ __('messages.generated_on') }}: {{ now()->format('Y-m-d H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
                <tr>
                    @foreach(array_keys($columns) as $field)
                        <td>
                            @if(str_contains($field, '_at') || str_contains($field, '_date'))
                                {{ \Carbon\Carbon::parse($item->$field)->format('Y-m-d') }}
                            @elseif($field === 'item' && $item->item)
                                {{ $item->item->name }}
                            @elseif($field === 'item_code' && $item->item)
                                {{ $item->item->code }}
                            @elseif($field === 'department' && $item->department)
                                {{ $item->department->name }}
                            @elseif(($field === 'requested_by' || $field === 'requester') && $item->requester)
                                {{ $item->requester->name }}
                            @elseif($field === 'unit' && $item->unit)
                                {{ $item->unit->name }}
                            @elseif($field === 'status')
                                <span class="status status-{{ $item->status }}">
                                    {{ __("messages.$item->status") }}
                                </span>
                            @else
                                {{ $item->$field ?? 'N/A' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ config('app.name') }} - {{ __('messages.page') }} {PAGENO} / {nb}
    </div>
</body>
</html>
