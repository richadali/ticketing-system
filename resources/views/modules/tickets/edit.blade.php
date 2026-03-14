@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Edit Ticket</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit Ticket #{{ $ticket->id }}</h5>

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('tickets.updateDetails', $ticket) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="name" class="form-label"><b>Ticket Name/Subject</b></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name', $ticket->name) }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="description" class="form-label"><b>Description</b></label>
                                    <textarea class="form-control" id="description" name="description" rows="5"
                                        required>{{ old('description', $ticket->description) }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="category" class="form-label"><b>Category</b></label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="" disabled {{ old('category', $ticket->category) ? '' :
                                            'selected' }}>Select Category</option>
                                        <option value="Whitelabel" {{ old('category', $ticket->category)=='Whitelabel' ?
                                            'selected' : '' }}>Whitelabel</option>
                                        <option value="Reports" {{ old('category', $ticket->category)=='Reports' ?
                                            'selected' : '' }}>Reports</option>
                                        <option value="Website" {{ old('category', $ticket->category)=='Website' ?
                                            'selected' : '' }}>Website</option>
                                        <option value="Email" {{ old('category', $ticket->category)=='Email' ?
                                            'selected' : '' }}>Email</option>
                                        <option value="Domain" {{ old('category', $ticket->category)=='Domain' ?
                                            'selected' : '' }}>Domain</option>
                                        <option value="Others" {{ old('category', $ticket->category)=='Others' ?
                                            'selected' : '' }}>Others</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="sub_company" class="form-label"><b>Sub-Company</b></label>
                                    <select class="form-select" id="sub_company" name="sub_company" required>
                                        <option value="" disabled {{ old('sub_company', $ticket->sub_company) ? '' :
                                            'selected' }}>Select Sub-Company</option>
                                        <option value="CG" {{ old('sub_company', $ticket->sub_company)=='CG' ?
                                            'selected' : '' }}>CG</option>
                                        <option value="Teesprint" {{ old('sub_company', $ticket->
                                            sub_company)=='Teesprint' ? 'selected' : '' }}>Teesprint</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="deadline" class="form-label"><b>Deadline</b></label>
                                    <input type="date" class="form-control" id="deadline" name="deadline"
                                        value="{{ old('deadline', $ticket->deadline ? $ticket->deadline->format('Y-m-d') : '') }}"
                                        required>
                                    <small class="text-muted">Set a target date for this ticket to be resolved.</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="urgent" name="urgent"
                                            value="1" {{ old('urgent', $ticket->urgent) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="urgent">Urgent</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="attachments" class="form-label"><b>Attachments</b></label>
                                    <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                                    <small class="text-muted">You can upload multiple files (max 2MB each).</small>
                                </div>
                            </div>

                            @if($ticket->attachments->count() > 0)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label"><b>Current Attachments</b></label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($ticket->attachments as $attachment)
                                        <div class="attachment-preview position-relative"
                                            id="attachment-{{ $attachment->id }}">
                                            @php
                                            $fileExtension = pathinfo($attachment->attachment_loc, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif',
                                            'bmp']);
                                            @endphp

                                            @if($isImage)
                                            <a href="{{ asset($attachment->attachment_loc) }}" target="_blank">
                                                <img src="{{ asset($attachment->attachment_loc) }}" alt="Attachment"
                                                    class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                                            </a>
                                            @else
                                            <a href="{{ asset($attachment->attachment_loc) }}" target="_blank"
                                                class="d-flex flex-column align-items-center justify-content-center p-3 border rounded"
                                                style="width: 150px; height: 150px;">
                                                <i class="bi bi-file-earmark-zip fs-1"></i>
                                                <span
                                                    class="text-muted mt-2">{{ basename($attachment->attachment_loc) }}</span>
                                            </a>
                                            @endif

                                            <button type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-attachment-btn"
                                                data-id="{{ $attachment->id }}" data-url="{{ route('attachments.destroy', $attachment->id) }}">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Update Ticket Details</button>
                            </div>
                        </form>

                        <hr>
                        {{-- Details Row: Assigned To & Quick Status Change --}}
                        <div class="row">
                            {{-- Assigned To --}}
                            <div class="col-md-6">
                                <div class="card border-light shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <h6 class="card-title mb-0 me-2"><i class="bi bi-person-fill me-1"></i>
                                                Assigned To:
                                            </h6>
                                            <span class="card-text fw-semibold">
                                                {{ $ticket->assignedTo ? $ticket->assignedTo->name : 'Unassigned' }}
                                            </span>
                                        </div>
                                        <form action="{{ route('tickets.assign-to-admin', $ticket) }}" method="POST"
                                            class="d-flex">
                                            @csrf
                                            <select name="assigned_to" class="form-select me-2" style="width: auto;">
                                                <option value="">-- Select User --</option>
                                                @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ $ticket->assigned_to ==
                                                    $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="bi bi-person-check"></i> Assign
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick Status Change --}}
                            <div class="col-md-6">
                                @include('modules.tickets._quick-status-change', ['ticket' => $ticket])
                            </div>
                        </div>

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
                                                <small class="text-muted">({{ $comment->created_at->format('d M Y, h:i
                                                    A')
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
                                        <strong>Add Comment</strong>
                                    </div>
                                    <div class="card-body pt-2">
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
                    </div>
                </div>
            </div>
        </div>
    </section>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
</main>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.remove-attachment-btn').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to delete this attachment?')) {
                    return;
                }

                const attachmentId = this.dataset.id;
                const url = this.dataset.url;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`attachment-${attachmentId}`).remove();
                        // Optionally, show a success message
                    } else {
                        // Optionally, show an error message
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Optionally, show an error message
                });
            });
        });
    });
</script>
@endpush