<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header .info {
            font-size: 12px;
            color: #555;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-warning {
            background-color: #ffc107;
            color: black;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: white;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
            padding: 3px 5px;
            border-radius: 3px;
            font-size: 9px;
        }

        .text-muted {
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <div class="info">Period: {{ $startDate }} to {{ $endDate }}</div>
        <div class="info">Generated on: {{ $generatedAt }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Created By</th>
                <th>Category</th>
                <th>Assigned To</th>
                <th>Created At</th>
                @if(isset($status) && $status == 'closed')
                <th>Closed At</th>
                @else
                <th>Last Updated</th>
                <th>Deadline</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $key => $ticket)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>
                    {{ $ticket->name }}
                    @if($ticket->urgent)
                    <span class="badge-danger" style="margin-left: 5px;">Urgent</span>
                    @endif
                </td>
                <td>{{ $ticket->creator ? $ticket->creator->name : 'Unknown' }}</td>
                <td>{{ $ticket->category ?? '-' }}</td>
                <td>{{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned' }}</td>
                <td>{{ $ticket->created_at->format('d M Y, h:i A') }}</td>

                @if(isset($status) && $status == 'closed')
                <td>
                    @if($ticket->closed_at)
                    @if(is_string($ticket->closed_at))
                    {{ $ticket->closed_at }}
                    @else
                    {{ $ticket->closed_at->format('d M Y, h:i A') }}
                    @endif
                    @else
                    -
                    @endif
                </td>
                @else
                <td>
                    {{ $ticket->updated_at->format('d M Y, h:i A') }}
                </td>
                <td>
                    @if($ticket->deadline)
                    @php
                    $deadlineDate = $ticket->deadline;
                    $today = \Carbon\Carbon::now()->startOfDay();
                    $daysLeft = $today->diffInDays($deadlineDate, false);
                    @endphp

                    @if($daysLeft < 0) <span class="badge-danger">
                        {{ $ticket->deadline->format('d M Y') }} (Overdue)
                        </span>
                        @elseif($daysLeft <= 2) <span class="badge-warning">
                            {{ $ticket->deadline->format('d M Y') }} (Soon)
                            </span>
                            @else
                            <span class="badge-info">
                                {{ $ticket->deadline->format('d M Y') }}
                            </span>
                            @endif
                            @else
                            -
                            @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">No tickets found for the selected period</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Copyright © {{ date('Y') }} Artec Engine Ticketing System</p>
    </div>
</body>

</html>