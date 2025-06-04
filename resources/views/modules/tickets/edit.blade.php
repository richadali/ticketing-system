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

                            @if($role == 'Admin')
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label"><b>Status</b></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="open" {{ old('status', $ticket->status) == 'open' ? 'selected' :
                                            '' }}>Open</option>
                                        <option value="in_progress" {{ old('status', $ticket->status) == 'in_progress' ?
                                            'selected' : '' }}>In Progress</option>
                                        <option value="closed" {{ old('status', $ticket->status) == 'closed' ?
                                            'selected' : '' }}>Closed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="assigned_to" class="form-label"><b>Assign To</b></label>
                                    <select class="form-select" id="assigned_to" name="assigned_to">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to', $ticket->assigned_to) ==
                                            $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
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