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

                        <form action="{{ route('tickets.update', $ticket) }}" method="POST">
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
                                <div class="col-md-12">
                                    <label for="deadline" class="form-label"><b>Deadline</b></label>
                                    <input type="date" class="form-control" id="deadline" name="deadline"
                                        value="{{ old('deadline', $ticket->deadline ? $ticket->deadline->format('Y-m-d') : '') }}"
                                        required>
                                    <small class="text-muted">Set a target date for this ticket to be resolved.</small>
                                </div>
                            </div>

                            @if($role == 'Admin')
                            <input type="hidden" name="status" value="{{ $ticket->status }}">
                            <input type="hidden" name="assigned_to" value="{{ $ticket->assigned_to }}">
                            @else
                            <input type="hidden" name="status" value="open">
                            <input type="hidden" name="assigned_to" value="{{ $ticket->assigned_to }}">
                            @endif

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Update Ticket</button>
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
</main>

@endsection