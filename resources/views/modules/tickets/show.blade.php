@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Ticket Details</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{-- Top Header: Title, Status, Priority --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Ticket #{{ $ticket->id }}</h5>
                            <div class="d-flex align-items-center">
                                {{-- Status Badge --}}
                                <span class="badge bg-{{
                                    $ticket->status == 'open' ? 'success' : 
                                    ($ticket->status == 'in_progress' ? 'warning' : 'secondary')
                                }} fs-6 me-2">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                                {{-- Priority Badge (moved up) --}}
                                @if($ticket->urgent)
                                <span class="badge bg-danger fs-6">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Urgent
                                </span>
                                @endif
                            </div>
                        </div>

                        {{-- Ticket Meta: Name, Creator, Category, Sub-Company --}}
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4 class="mb-1">{{ $ticket->name }}</h4>
                                <p class="text-muted small">Created on {{ $ticket->created_at->format('d M Y, h:i A') }}
                                    by {{
                                    $ticket->creator ? $ticket->creator->name : 'Unknown'
                                    }}</p>
                                <div class="mt-2">
                                    {{-- Category Badge --}}
                                    <span class="text-muted small">Category:</span>
                                    <span class="badge bg-info p-2 me-1">
                                        <i class="bi bi-tag me-1"></i>
                                        {{ $ticket->category }}
                                    </span>
                                    {{-- Sub-Company Badge --}}
                                    <span class="text-muted small">Sub-Company:</span>
                                    <span class="badge bg-secondary p-2">
                                        <i class="bi bi-building me-1"></i>
                                        {{ $ticket->sub_company }}
                                    </span>
                                    @if($ticket->deadline)
                                    <span class="text-muted small">Deadline:</span>
                                    @php
                                    $deadlineDate = $ticket->deadline;
                                    $today = \Carbon\Carbon::now()->startOfDay();
                                    $daysLeft = $today->diffInDays($deadlineDate, false);
                                    @endphp

                                    @if($daysLeft < 0) <span class="badge bg-danger p-2">
                                        <i class="bi bi-calendar-x me-1"></i>
                                        {{ $ticket->deadline->format('d M Y') }}
                                        <small>({{ abs($daysLeft) }} days overdue)</small>
                                        </span>
                                        @elseif($daysLeft <= 2) <span class="badge bg-warning p-2">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            {{ $ticket->deadline->format('d M Y') }}
                                            <small>({{ $daysLeft }} days left)</small>
                                            </span>
                                            @else
                                            <span class="badge bg-info p-2">
                                                <i class="bi bi-calendar me-1"></i>
                                                {{ $ticket->deadline->format('d M Y') }}
                                                <small>({{ $daysLeft }} days left)</small>
                                            </span>
                                            @endif
                                </div>
                            </div>
                            @endif

                            @if($ticket->status == 'closed' && $ticket->closed_at)
                            <span class="text-muted small">Closed At:</span>
                            <span class="badge bg-secondary p-2">
                                <i class="bi bi-lock-fill me-1"></i>
                                @if(is_string($ticket->closed_at))
                                {{ $ticket->closed_at }}
                                @else
                                {{ $ticket->closed_at->format('d M Y, h:i A') }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif
                </div>

                <hr>

                {{-- Details Row: Assigned To & Quick Status Change --}}
                <div class="row mb-4">
                    {{-- Assigned To --}}
                    <div class="col-md-6">
                        <div class="card border-light shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <h6 class="card-title mb-0 me-2"><i class="bi bi-person-fill me-1"></i> Assigned To:
                                    </h6>
                                    <span class="card-text fw-semibold">
                                        {{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned' }}
                                    </span>
                                </div>

                                @if($role === 'Admin')
                                <form action="{{ route('tickets.assign-to-admin', $ticket) }}" method="POST"
                                    class="d-flex">
                                    @csrf
                                    <select name="assigned_to" class="form-select me-2" style="width: auto;">
                                        <option value="">-- Select Admin --</option>
                                        @foreach($adminUsers as $admin)
                                        <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ?
                                            'selected' : '' }}>
                                            {{ $admin->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-person-check"></i> Assign
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Quick Status Change (Admin Only) --}}
                    @if($role === 'Admin')
                    <div class="col-md-6">
                        <div class="card border-light shadow-sm">
                            <div class="card-body p-3">
                                <h6 class="card-title mb-2"><i class="bi bi-arrow-repeat me-1"></i> Quick Status Change
                                </h6>
                                <form action="{{ route('tickets.change-status', $ticket) }}" method="POST"
                                    class="d-flex">
                                    @csrf
                                    <select name="status" class="form-select me-2" style="width: auto;">
                                        <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open
                                        </option>
                                        <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' :
                                            '' }}>In Progress</option>
                                        <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : ''
                                            }}>Closed</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>


                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>Description</strong>
                            </div>
                            <div class="card-body">
                                <p>{{ $ticket->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>Comments</strong>
                            </div>
                            <div class="card-body">
                                @if($ticket->comments->count() > 0)
                                @foreach($ticket->comments as $comment)
                                <div class="mb-3">
                                    <p class="mb-1">
                                        <strong>{{ $comment->user->name }}</strong>
                                        <small class="text-muted">({{ $comment->created_at->format('d M Y, h:i A')
                                            }})</small>
                                    </p>
                                    {!! $comment->body !!}
                                </div>
                                @if(!$loop->last)
                                <hr>
                                @endif
                                @endforeach
                                @else
                                <p class="text-muted">No comments yet.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($role === 'Admin')
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>Add Comment</strong>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('tickets.add-comment', $ticket) }}" method="POST">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <textarea class="form-control" id="comment" name="comment"
                                                rows="5"></textarea>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Submit Comment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($ticket->attachments->count() > 0)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>Attachments</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($ticket->attachments as $attachment)
                                    <div class="col-md-3 mb-3">
                                        <a href="{{ asset($attachment->attachment_loc) }}" target="_blank">
                                            <img src="{{ asset($attachment->attachment_loc) }}"
                                                class="img-fluid rounded" alt="Attachment">
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <strong>Activity History</strong>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    @forelse ($ticket->activities as $activity)
                                    <div class="timeline-item">
                                        <div class="timeline-marker">
                                            @switch($activity->activity_type)
                                            @case('created')
                                            <i class="bi bi-plus-circle text-success"></i>
                                            @break
                                            @case('status_changed')
                                            <i class="bi bi-check-circle text-primary"></i>
                                            @break
                                            @case('assigned')
                                            <i class="bi bi-person text-info"></i>
                                            @break
                                            @default
                                            <i class="bi bi-info-circle"></i>
                                            @endswitch
                                        </div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1">{{ $activity->description }}</h6>
                                                <small class="text-muted">{{ $activity->created_at->format('M d,
                                                    Y h:i A') }}</small>
                                            </div>
                                            <small class="text-muted">
                                                By {{ $activity->user ? $activity->user->name : 'System' }}
                                            </small>
                                        </div>
                                    </div>
                                    <hr>
                                    @empty
                                    <p class="text-muted">No activity recorded for this ticket.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Back to Tickets</a>

                        @if(($role == 'Admin' || Auth::id() == $ticket->created_by) && $ticket->status ==
                        'open')
                        <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-primary">Edit
                            Ticket</a>
                        @endif
                        @if($role == 'Admin')
                        <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to delete this ticket?')">
                                Delete Ticket
                            </button>
                        </form>
                        @endif
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
        tinymce.init({
            selector: '#comment',
            menubar: false,
            plugins: ["preview", "code", "wordcount"],
            toolbar: [
                "undo redo | bold italic underline strikethrough | fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist",
            ],
            toolbar_sticky: true,
            branding: false,
            quickbars_selection_toolbar:
                "bold italic | blockquote | alignleft aligncenter alignright alignjustify",
            height: 200,
        });
    });
                    </script>
                </div>
            </div>
        </div>
        </div>
        </div>
    </section>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
</main>

<style>
    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline-item {
        display: flex;
        margin-bottom: 15px;
    }

    .timeline-marker {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
        border: 1px solid #dee2e6;
    }

    .timeline-marker i {
        font-size: 1.2rem;
    }

    .timeline-content {
        flex-grow: 1;
    }
</style>

@endsection