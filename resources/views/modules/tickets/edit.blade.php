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