@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Tickets Reports</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Ticket Management</li>
                <li class="breadcrumb-item">Reports</li>
                <li class="breadcrumb-item active">{{ ucfirst($status) }} Tickets</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">{{ $reportTitle }}</h5>
                            <div>
                                <form action="{{ route('tickets.reports', ['status' => $status]) }}" method="GET"
                                    class="d-inline">
                                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                                    <input type="hidden" name="export_pdf" value="1">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-file-earmark-pdf"></i> Export to PDF
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <form action="{{ route('tickets.reports', ['status' => $status]) }}" method="GET"
                                    class="d-flex align-items-center flex-wrap">
                                    <div class="me-3 mb-2">
                                        <label for="start_date" class="form-label">From Date</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control"
                                            value="{{ $startDate }}">
                                    </div>
                                    <div class="me-3 mb-2">
                                        <label for="end_date" class="form-label">To Date</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control"
                                            value="{{ $endDate }}">
                                    </div>
                                    <div class="me-3 mb-2">
                                        <label class="form-label" style="visibility: hidden;">Action</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped tickets-report-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Created By</th>
                                        <th class="text-center">Assigned To</th>
                                        <th class="text-center">Created At</th>
                                        @if($status == 'closed')
                                        <th class="text-center">Closed At</th>
                                        <th class="text-center">Resolution Time</th>
                                        @else
                                        <th class="text-center">Last Updated</th>
                                        <th class="text-center">Deadline</th>
                                        @endif
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tickets as $key => $ticket)
                                    <tr>
                                        <td class="text-center">{{ $key + 1 }}</td>
                                        <td>
                                            {{ $ticket->name }}
                                            @if($ticket->attachments && $ticket->attachments->count() > 0)
                                            <i class="bi bi-paperclip ms-1"
                                                title="{{ $ticket->attachments->count() }} attachment(s)"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $ticket->creator ? $ticket->creator->name : 'Unknown'
                                            }}</td>
                                        <td class="text-center">{{ $ticket->assignedTo ? $ticket->assignedTo->name :
                                            'Unassigned' }}</td>
                                        <td class="text-center">{{ $ticket->created_at->format('d M Y, h:i A') }}</td>

                                        @if($status == 'closed')
                                        <td class="text-center">
                                            @if($ticket->closed_at)
                                            @if(is_string($ticket->closed_at))
                                            {{ $ticket->closed_at }}
                                            @else
                                            {{ $ticket->closed_at->format('d M Y, h:i A') }}
                                            @endif
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($ticket->closed_at)
                                            @php
                                            if(is_string($ticket->closed_at)) {
                                            $closedAt = \Carbon\Carbon::parse($ticket->closed_at);
                                            } else {
                                            $closedAt = $ticket->closed_at;
                                            }
                                            $createdAt = $ticket->created_at;
                                            $diffInHours = $createdAt->diffInHours($closedAt);
                                            $diffInDays = floor($diffInHours / 24);
                                            $remainingHours = $diffInHours % 24;
                                            @endphp
                                            @if($diffInDays > 0)
                                            {{ $diffInDays }}d {{ $remainingHours }}h
                                            @else
                                            {{ $diffInHours }}h
                                            @endif
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        @else
                                        <td class="text-center">
                                            {{ $ticket->updated_at->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="text-center">
                                            @if($ticket->deadline)
                                            @php
                                            $deadlineDate = $ticket->deadline;
                                            $today = \Carbon\Carbon::now()->startOfDay();
                                            $daysLeft = $today->diffInDays($deadlineDate, false);
                                            @endphp

                                            @if($daysLeft < 0) <span class="badge bg-danger p-2">
                                                {{ $ticket->deadline->format('d M Y') }}
                                                <small>(Overdue)</small>
                                                </span>
                                                @elseif($daysLeft <= 2) <span class="badge bg-warning p-2">
                                                    {{ $ticket->deadline->format('d M Y') }}
                                                    <small>(Soon)</small>
                                                    </span>
                                                    @else
                                                    <span class="badge bg-info p-2">
                                                        {{ $ticket->deadline->format('d M Y') }}
                                                    </span>
                                                    @endif
                                                    @else
                                                    <span class="text-muted">-</span>
                                                    @endif
                                        </td>
                                        @endif

                                        <td class="text-center">
                                            <a href="{{ route('tickets.show', $ticket->id) }}"
                                                class="btn btn-info btn-sm" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="empty-row">
                                        <td colspan="8" class="text-center">No {{ strtolower($status) }} tickets found
                                            for the selected period</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
</main>

<script>
    $(document).ready(function() {
        // Check if we have actual data rows (not just the empty message)
        var hasData = $('.tickets-report-table tbody tr').length > 0 && 
                      !$('.tickets-report-table tbody tr#empty-row').length;
                      
        if (hasData) {
            $('.tickets-report-table').DataTable({
                destroy: true,
                processing: true,
                select: true,
                paging: true,
                lengthChange: true,
                searching: true,
                info: true,
                responsive: true,
                autoWidth: false,
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 7 } // Disable sorting on the Actions column
                ]
            });
        } else {
            // For empty tables, add a simple class to maintain styling without DataTable initialization
            $('.tickets-report-table').addClass('table-hover');
        }
    });
</script>

@endsection