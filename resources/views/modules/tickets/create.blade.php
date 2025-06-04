@extends('layouts.app_1')

@section('content')

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Create Ticket</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Create New Ticket</h5>

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="name" class="form-label"><b>Ticket Name/Subject</b></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name') }}" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="description" class="form-label"><b>Description</b></label>
                                    <textarea class="form-control" id="description" name="description" rows="5"
                                        required>{{ old('description') }}</textarea>
                                    <small class="text-muted">Please provide detailed information about your
                                        issue.</small>
                                </div>
                            </div>

                            <!-- Removed Assign To field -->

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="attachments" class="form-label"><b>Attachments (Optional)</b></label>
                                    <input type="file" class="form-control" id="attachments" name="attachments[]"
                                        multiple accept="image/*">
                                    <small class="text-muted">You can upload multiple images (JPEG, PNG, JPG, GIF only,
                                        max 2MB each).</small>
                                    @if ($errors->has('attachments.*'))
                                    <div class="text-danger mt-1">
                                        @foreach($errors->get('attachments.*') as $error)
                                        <span>{{ $error[0] }}</span><br>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Submit Ticket</button>
                                <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const attachmentsInput = document.getElementById('attachments');
        const maxFileSize = 2 * 1024 * 1024; // 2MB in bytes
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        
        attachmentsInput.addEventListener('change', function() {
            let validFiles = true;
            let errorMessage = '';
            
            if (this.files.length > 0) {
                Array.from(this.files).forEach(file => {
                    if (!allowedTypes.includes(file.type)) {
                        validFiles = false;
                        errorMessage = `File "${file.name}" is not a valid image type. Only JPEG, PNG, JPG, and GIF are allowed.`;
                        return;
                    }
                    
                    if (file.size > maxFileSize) {
                        validFiles = false;
                        errorMessage = `File "${file.name}" exceeds the maximum size of 2MB.`;
                        return;
                    }
                });
                
                if (!validFiles) {
                    alert(errorMessage);
                    this.value = ''; // Clear the input
                }
            }
        });
    });
</script>

@endsection