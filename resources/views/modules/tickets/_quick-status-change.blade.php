<div class="card border-light shadow-sm">
    <div class="card-body p-3">
        <h6 class="card-title mb-2"><i class="bi bi-arrow-repeat me-1"></i> Quick
            Status Change
        </h6>
        <form action="{{ route('tickets.change-status', $ticket) }}" method="POST"
            class="d-flex">
            @csrf
            <select name="status" class="form-select me-2" style="width: auto;">
                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>
                    Open
                </option>
                <option value="in_progress" {{ $ticket->status == 'in_progress' ?
                    'selected' : '' }}>In Progress</option>
                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : ''
                    }}>Closed</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Update</button>
        </form>
    </div>
</div>