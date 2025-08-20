@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Tickets</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Ticket Management</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">All Tickets</h5>
                            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create Ticket
                            </a>
                        </div>

                        @if(isset($statuses))
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <form action="{{ route('tickets.index') }}" method="GET"
                                    class="d-flex align-items-center flex-wrap">
                                    <div class="me-3 mb-2">
                                        <select name="status" class="form-select">
                                            @foreach($statuses as $key => $value)
                                            <option value="{{ $key }}" {{ request('status')==$key ? 'selected' : '' }}>
                                                {{
                                                $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if($role == 'Admin' && isset($adminFilters))
                                    <div class="me-3 mb-2">
                                        <select name="filter" class="form-select">
                                            @foreach($adminFilters as $key => $value)
                                            <option value="{{ $key }}" {{ request('filter')==$key ? 'selected' : '' }}>
                                                {{
                                                $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif

                                    <div class="form-check form-switch me-3 mb-2">
                                        <input class="form-check-input" type="checkbox" name="show_closed"
                                            id="showClosed" {{ isset($showClosed) && $showClosed ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showClosed"><strong>Show Closed
                                                Tickets</strong></label>
                                    </div>

                                    <button type="submit" class="btn btn-primary mb-2">Apply Filters</button>
                                </form>
                            </div>
                        </div>
                        @endif

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
                            <table class="table table-bordered table-striped tickets-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Assigned To</th>
                                        <th class="text-center">Created At</th>
                                        <th class="text-center">Closed At</th>
                                        <th class="text-center">Deadline</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tickets as $key => $ticket)
                                    <tr>
                                        <td class="text-center">{{ $key + 1 }}</td>
                                        <td class="text-center">
                                            {{ $ticket->name }}
                                            @if($ticket->urgent)
                                            <span class="badge bg-danger ms-1">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                Urgent
                                            </span>
                                            @endif

                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ 
                                                $ticket->status == 'open' ? 'success' : 
                                                ($ticket->status == 'in_progress' ? 'warning' : 'secondary')
                                            }} p-2">
                                                @if($ticket->status == 'closed')
                                                <i class="bi bi-lock-fill me-1"></i>
                                                @elseif($ticket->status == 'open')
                                                <i class="bi bi-exclamation-circle-fill me-1"></i>
                                                @elseif($ticket->status == 'in_progress')
                                                <i class="bi bi-hourglass-split me-1"></i>
                                                @else
                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                @endif
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $ticket->assignedTo ? $ticket->assignedTo->name :
                                            'Unassigned' }}</td>
                                        <td class="text-center">{{ $ticket->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-center">
                                            @if($ticket->closed_at)
                                            <span class="badge bg-secondary p-2">
                                                @if(is_string($ticket->closed_at))
                                                {{ $ticket->closed_at }}
                                                @else
                                                {{ $ticket->closed_at->format('d M Y, h:i A') }}
                                                @endif
                                            </span>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
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
                                        <td class="text-center">
                                            <button type="button" class="btn btn-info btn-sm view-ticket-btn"
                                                data-bs-toggle="modal" data-bs-target="#viewTicketModal"
                                                data-ticket-id="{{ $ticket->id }}" title="View Ticket Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if(($role == 'Admin' || Auth::id() == $ticket->created_by))
                                            <a href="{{ route('tickets.edit', $ticket->id) }}"
                                                class="btn btn-primary btn-sm" title="Edit Ticket">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @endif
                                            @if($role == 'Admin')
                                            <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    title="Delete Ticket"
                                                    onclick="return confirm('Are you sure you want to delete this ticket?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="empty-row">
                                        <td colspan="8" class="text-center">No tickets found</td>
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

    <!-- View Ticket Modal -->
    <div class="modal fade" id="viewTicketModal" tabindex="-1" aria-labelledby="viewTicketModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTicketModalLabel">Ticket Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Ticket details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
</main>

<script>
    $(document).ready(function() {
        // Check if we have actual data rows (not just the empty message)
        var hasData = $('.tickets-table tbody tr').length > 0 &&
            !$('.tickets-table tbody tr#empty-row').length;

        if (hasData) {
            // Create a custom sorting function for deadline + urgent
            $.fn.dataTable.ext.order['deadline-urgent'] = function(settings, col) {
                return this.api().column(col, {
                    order: 'index'
                }).nodes().map(function(td, i) {
                    // Get the deadline date from the cell
                    var deadlineText = $(td).text().trim();
                    var deadlineDate = deadlineText !== '-' ? deadlineText.split(' ')[0] + ' ' +
                        deadlineText.split(' ')[1] + ' ' + deadlineText.split(' ')[2] :
                        '9999-12-31';

                    // Check if the row has an urgent badge
                    var row = $(td).closest('tr');
                    var isUrgent = row.find('td:eq(1) .badge.bg-danger').length > 0 ? '0' :
                        '1'; // 0 sorts before 1

                    // Return a sortable string: deadline date + urgent flag
                    return deadlineDate + '_' + isUrgent;
                });
            };

            $('.tickets-table').DataTable({
                destroy: true,
                processing: true,
                select: true,
                paging: true,
                lengthChange: true,
                searching: true,
                info: true,
                responsive: true,
                autoWidth: false,
                order: [
                    [6, 'asc']
                ], // Sort by Deadline (ascending) by default
                columnDefs: [{
                    orderable: false,
                    targets: 7
                }, // Disable sorting on the Actions column
                {
                    // Custom sorting for the Deadline column
                    targets: 6,
                    type: 'deadline-urgent'
                }
                ],
                // Add custom sorting to prioritize urgent tickets
                createdRow: function(row, data, dataIndex) {
                    // Get the urgent status from the name column
                    var isUrgent = $(row).find('td:eq(1) .badge.bg-danger').length > 0;

                    // Add a class to urgent rows for styling
                    if (isUrgent) {
                        $(row).addClass('urgent-ticket');
                    }
                }
            });
        } else {
            // For empty tables, add a simple class to maintain styling without DataTable initialization
            $('.tickets-table').addClass('table-hover');
        }

        // Handle modal trigger
        $('.view-ticket-btn').on('click', function() {
            var ticketId = $(this).data('ticket-id');
            var modalBody = $('#viewTicketModal .modal-body');

            // Clear previous content and show a loader
            modalBody.html('<p class="text-center">Loading...</p>');

            // Fetch ticket details via AJAX
            $.ajax({
                url: '/tickets/' + ticketId + '/details',
                method: 'GET',
                success: function(response) {
                    modalBody.html(response);
                },
                error: function() {
                    modalBody.html(
                        '<p class="text-danger text-center">Failed to load ticket details.</p>'
                        );
                }
            });
        });
    });
</script>

@endsection