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
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="mb-1">{{ $ticket->name }}</h4>
                <p class="text-muted small">Created on {{ $ticket->created_at->format('d M Y, h:i A') }}
                    by {{ $ticket->creator ? $ticket->creator->name : 'Unknown' }}
                </p>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-2">
            <div class="d-flex align-items-center">
                {{-- Category Badge --}}
                <span class="text-muted small me-2">Category:</span>
                <span class="badge bg-info p-2 me-3">
                    <i class="bi bi-tag me-1"></i>
                    {{ $ticket->category }}
                </span>
                {{-- Sub-Company Badge --}}
                <span class="text-muted small me-2">Sub-Company:</span>
                <span class="badge bg-secondary p-2 me-3">
                    <i class="bi bi-building me-1"></i>
                    {{ $ticket->sub_company }}
                </span>
                @if($ticket->deadline)
                <span class="text-muted small me-2">Deadline:</span>
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
                        @endif
            </div>
            <div class="d-flex align-items-center">
                <h6 class="me-2 mb-0"><i class="bi bi-person-fill me-1"></i> Assigned To:</h6>
                <span class="fw-semibold">
                    {{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned' }}
                </span>
            </div>
        </div>
    </div>

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

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong>Description</strong>
            </div>
            <div class="card-body pt-2">
                <p>{!! nl2br(e($ticket->description)) !!}</p>
            </div>
        </div>
    </div>
</div>

@if($ticket->attachments->count() > 0)
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong>Attachments</strong>
            </div>
            <div class="card-body pt-4">
                <div class="row">
                    @foreach($ticket->attachments as $attachment)
                    <div class="col-md-3 mb-3">
                        @php
                            $fileExtension = pathinfo($attachment->attachment_loc, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']);
                        @endphp

                        @if($isImage)
                            <a href="{{ asset($attachment->attachment_loc) }}" target="_blank">
                                <img src="{{ asset($attachment->attachment_loc) }}" class="img-fluid rounded" alt="Attachment">
                            </a>
                        @else
                            <a href="{{ asset($attachment->attachment_loc) }}" target="_blank" class="d-flex flex-column align-items-center">
                                @if(strtolower($fileExtension) == 'pdf')
                                    <i class="bi bi-file-earmark-pdf" style="font-size: 48px;"></i>
                                @else
                                    <i class="bi bi-file-earmark" style="font-size: 48px;"></i>
                                @endif
                                <span>Click to open</span>
                            </a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong>Comments</strong>
            </div>
            <div class="card-body pt-2">
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

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong>Activity History</strong>
            </div>
            <div class="card-body pt-2">
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