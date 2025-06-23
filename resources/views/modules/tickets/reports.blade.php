@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Tickets Reports</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Ticket Management</li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Closed Tickets Report</h5>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <form action="{{ route('tickets.reports') }}" method="GET"
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
                                        <th class="text-center">Closed At</th>
                                        <th class="text-center">Resolution Time</th>
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
                                        <td class="text-center">
                                            <a href="{{ route('tickets.show', $ticket->id) }}"
                                                class="btn btn-info btn-sm" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="empty-row">
                                        <td colspan="8" class="text-center">No closed tickets found for the selected
                                            period</td>
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
                order: [[5, 'desc']], // Sort by Closed At column by default
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