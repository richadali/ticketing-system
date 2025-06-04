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
                            <div class="col-md-6">
                                <form action="{{ route('tickets.index') }}" method="GET" class="d-flex">
                                    <select name="status" class="form-select me-2">
                                        @foreach($statuses as $key => $value)
                                        <option value="{{ $key }}" {{ request('status')==$key ? 'selected' : '' }}>{{
                                            $value }}</option>
                                        @endforeach
                                    </select>

                                    @if($role == 'Admin' && isset($adminFilters))
                                    <select name="filter" class="form-select me-2">
                                        @foreach($adminFilters as $key => $value)
                                        <option value="{{ $key }}" {{ request('filter')==$key ? 'selected' : '' }}>{{
                                            $value }}</option>
                                        @endforeach
                                    </select>
                                    @endif

                                    <button type="submit" class="btn btn-primary">Filter</button>
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
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tickets as $key => $ticket)
                                    <tr>
                                        <td class="text-center">{{ $key + 1 }}</td>
                                        <td class="text-center">
                                            {{ $ticket->name }}
                                            @if($ticket->attachments && $ticket->attachments->count() > 0)
                                            <i class="bi bi-paperclip ms-1"
                                                title="{{ $ticket->attachments->count() }} attachment(s)"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ 
                                                $ticket->status == 'open' ? 'success' : 
                                                ($ticket->status == 'in_progress' ? 'warning' : 
                                                ($ticket->status == 'resolved' ? 'info' : 'secondary')) 
                                            }}">
                                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $ticket->assignedTo ? $ticket->assignedTo->name :
                                            'Unassigned' }}</td>
                                        <td class="text-center">{{ $ticket->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('tickets.show', $ticket->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="bi bi-eye"></i> View
                                            </a>

                                            @if(($role == 'Admin' || Auth::id() == $ticket->created_by) &&
                                            $ticket->status == 'open')
                                            <a href="{{ route('tickets.edit', $ticket->id) }}"
                                                class="btn btn-primary btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            @endif
                                            @if($role == 'Admin')
                                            <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure you want to delete this ticket?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="empty-row">
                                        <td colspan="6" class="text-center">No tickets found</td>
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
        var hasData = $('.tickets-table tbody tr').length > 0 && 
                      !$('.tickets-table tbody tr#empty-row').length;
                      
        if (hasData) {
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
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: 5 } // Disable sorting on the Actions column
                ]
            });
        } else {
            // For empty tables, add a simple class to maintain styling without DataTable initialization
            $('.tickets-table').addClass('table-hover');
        }
    });
</script>

@endsection