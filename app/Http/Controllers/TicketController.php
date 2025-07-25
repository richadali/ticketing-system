<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Start with a base query
        $query = Ticket::with(['assignedTo', 'attachments', 'creator']);

        // Filter by status if provided
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Show/hide closed tickets based on checkbox
        $showClosed = $request->has('show_closed');
        if (!$showClosed) {
            $query->where('status', '!=', 'closed');
        }

        // For regular users, only show tickets they created
        if ($role !== 'Admin') {
            $query->where('created_by', $user->id);
        } else if ($request->has('filter')) {
            // Admin-specific filters
            switch ($request->filter) {
                case 'assigned_to_me':
                    $query->where('assigned_to', $user->id);
                    break;
                case 'unassigned':
                    $query->whereNull('assigned_to');
                    break;
                case 'created_by_me':
                    $query->where('created_by', $user->id);
                    break;
            }
        }

        // Get tickets
        $tickets = $query->latest()->get();

        // Get statuses for filter dropdown
        $statuses = ['all' => 'All', 'open' => 'Open', 'in_progress' => 'In Progress', 'closed' => 'Closed'];

        // Admin-specific filters
        $adminFilters = [
            'all' => 'All Tickets',
            'assigned_to_me' => 'Assigned to Me',
            'unassigned' => 'Unassigned',
            'created_by_me' => 'Created by Me'
        ];

        return view('modules.tickets.index', compact('role', 'tickets', 'statuses', 'adminFilters', 'showClosed'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $role = Auth::user()->role ? Auth::user()->role->name : 'User';
        return view('modules.tickets.create', compact('role'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date|after_or_equal:today',
            'category' => 'required|string|in:Whitelabel,Reports,Website,Email,Domain,Others',
            'sub_company' => 'required|string|in:CG,Teesprint',
            'urgent' => 'nullable|boolean',
            'attachments.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $ticket = new Ticket();
        $ticket->name = $request->name;
        $ticket->description = $request->description;
        $ticket->status = 'open';
        $ticket->created_by = Auth::id();
        $ticket->deadline = $request->deadline;
        $ticket->category = $request->category;
        $ticket->sub_company = $request->sub_company;
        $ticket->urgent = $request->has('urgent');
        $ticket->save();

        // Record ticket creation activity
        $this->recordActivity($ticket, 'created', 'Ticket was created');

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/tickets'), $filename);

                $attachment = new \App\Models\Attachment();
                $attachment->ticket_id = $ticket->id;
                $attachment->attachment_loc = 'uploads/tickets/' . $filename;
                $attachment->save();
            }

            // Removed attachment activity tracking
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        $role = Auth::user()->role ? Auth::user()->role->name : 'User';
        $ticket->load('assignedTo', 'attachments', 'activities.user', 'creator');

        $adminUsers = [];
        if ($role === 'Admin') {
            $adminUsers = User::whereHas('role', function ($query) {
                $query->where('name', 'Admin');
            })->get();
        }

        return view('modules.tickets.show', compact('ticket', 'role', 'adminUsers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $ticket)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';
        $isCreator = $ticket->created_by == $user->id;

        // Only admins or the creator of the ticket can edit
        if ($role !== 'Admin' && !$isCreator) {
            return redirect()->route('tickets.show', $ticket)->with('error', 'You do not have permission to edit this ticket.');
        }

        // Only open tickets can be edited
        if ($ticket->status !== 'open') {
            return redirect()->route('tickets.show', $ticket)->with('error', 'Only tickets with "Open" status can be edited.');
        }

        $users = User::whereHas('role', function ($query) {
            $query->where('name', 'Admin');
        })->get();

        return view('modules.tickets.edit', compact('ticket', 'role', 'isCreator', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';
        $isCreator = $ticket->created_by == $user->id;

        // Only admins or the creator of the ticket can update
        if ($role !== 'Admin' && !$isCreator) {
            return redirect()->route('tickets.show', $ticket)->with('error', 'You do not have permission to update this ticket.');
        }

        // Only open tickets can be updated
        if ($ticket->status !== 'open') {
            return redirect()->route('tickets.show', $ticket)->with('error', 'Only tickets with "Open" status can be updated.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:open,in_progress,closed',
            'assigned_to' => 'nullable|exists:users,id',
            'deadline' => 'required|date',
        ]);

        // Regular users can only edit name, description and deadline, not status or assignment
        if ($role !== 'Admin') {
            // Track changes to name and description
            if ($ticket->name != $request->name || $ticket->description != $request->description || $ticket->deadline != $request->deadline) {
                $this->recordActivity(
                    $ticket,
                    'updated',
                    'Ticket details were updated'
                );
            }

            // Update only name and description
            $ticket->name = $request->name;
            $ticket->description = $request->description;
            $ticket->deadline = $request->deadline;
            $ticket->save();
        } else {
            // Track status change - only for closed
            if (($ticket->status != $request->status) && $request->status == 'closed') {
                $this->recordActivity(
                    $ticket,
                    'status_changed',
                    'Ticket was closed',
                    $ticket->status,
                    $request->status
                );

                // Set closed_at timestamp when ticket is closed
                $ticket->closed_at = now();
            }

            // Track assignment change
            if ($ticket->assigned_to != $request->assigned_to && $request->assigned_to) {
                $newAssignee = User::find($request->assigned_to)->name;

                $this->recordActivity(
                    $ticket,
                    'assigned',
                    'Ticket was assigned to ' . $newAssignee
                );
            }

            // Update ticket (all fields for admin)
            $ticket->name = $request->name;
            $ticket->description = $request->description;
            $ticket->status = $request->status;
            $ticket->assigned_to = $request->assigned_to;
            $ticket->deadline = $request->deadline;
            $ticket->save();
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Record an activity for a ticket.
     *
     * @param  \App\Models\Ticket  $ticket
     * @param  string  $activityType
     * @param  string  $description
     * @param  mixed  $oldValue
     * @param  mixed  $newValue
     * @return void
     */
    private function recordActivity(Ticket $ticket, $activityType, $description, $oldValue = null, $newValue = null)
    {
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'activity_type' => $activityType,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /**
     * Assign a ticket to the admin or change its status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function assignToMe(Ticket $ticket)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Only admins can assign tickets to themselves
        if ($role !== 'Admin') {
            return redirect()->route('tickets.show', $ticket)->with('error', 'You do not have permission to assign tickets.');
        }

        $oldAssignedTo = $ticket->assigned_to;
        $oldStatus = $ticket->status;

        // Update assigned_to and set status to in_progress
        $ticket->assigned_to = $user->id;
        $ticket->status = 'in_progress';
        $ticket->save();

        // Record assignment activity
        $this->recordActivity(
            $ticket,
            'assigned',
            'Ticket was self-assigned by ' . $user->name
        );

        // Record status change if status was not already in_progress
        if ($oldStatus !== 'in_progress') {
            $this->recordActivity(
                $ticket,
                'status_changed',
                'Ticket status changed to in_progress',
                $oldStatus,
                'in_progress'
            );
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket assigned to you successfully and set to In Progress.');
    }

    /**
     * Quick status change for a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Only admins can change status directly
        if ($role !== 'Admin') {
            return redirect()->route('tickets.show', $ticket)->with('error', 'You do not have permission to change ticket status.');
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
        ]);

        $oldStatus = $ticket->status;
        $ticket->status = $request->status;

        // Set closed_at timestamp when ticket is closed
        if ($request->status == 'closed' && $oldStatus != 'closed') {
            $ticket->closed_at = now();
        } elseif ($request->status != 'closed' && $oldStatus == 'closed') {
            // Clear closed_at if reopening a ticket
            $ticket->closed_at = null;
        }

        $ticket->save();

        // Record activity
        $this->recordActivity(
            $ticket,
            'status_changed',
            'Ticket status changed to ' . $request->status,
            $oldStatus,
            $request->status
        );

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Assign a ticket to another admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function assignToAdmin(Request $request, Ticket $ticket)
    {
        // Validate request
        $request->validate([
            'admin_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Check permissions
        if ($role !== 'Admin') {
            return redirect()->route('tickets.show', $ticket)
                ->with('error', 'Only admins can assign tickets.');
        }

        // Update ticket
        $oldAssignee = $ticket->assigned_to;
        $ticket->assigned_to = $request->admin_id;
        $ticket->save();

        // Record activity
        $newAdmin = User::find($request->admin_id);
        $oldAdmin = $oldAssignee ? User::find($oldAssignee) : null;

        $this->recordActivity(
            $ticket,
            'assigned',
            'Ticket was assigned to ' . $newAdmin->name,
            $oldAdmin ? $oldAdmin->name : 'Unassigned',
            $newAdmin->name
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket assigned successfully.');
    }

    /**
     * Display report of tickets filtered by status and date range
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Default to last 7 days
        $defaultStartDate = now()->subDays(7)->format('Y-m-d');
        $defaultEndDate = now()->format('Y-m-d');

        $startDate = $request->input('start_date', $defaultStartDate);
        $endDate = $request->input('end_date', $defaultEndDate);

        // Default status is closed if not provided
        $status = $request->input('status', 'closed');
        $validStatuses = ['open', 'in_progress', 'closed'];

        // Validate status parameter
        if (!in_array($status, $validStatuses)) {
            $status = 'closed';
        }

        // Build title and description based on status
        switch ($status) {
            case 'open':
                $reportTitle = 'Open Tickets Report';
                $dateField = 'created_at'; // For open tickets, we filter by creation date
                break;
            case 'in_progress':
                $reportTitle = 'In Progress Tickets Report';
                $dateField = 'updated_at'; // For in progress tickets, we use last update date
                break;
            case 'closed':
            default:
                $reportTitle = 'Closed Tickets Report';
                $dateField = 'closed_at'; // For closed tickets, we use closed date
                break;
        }

        // Start with a base query for tickets with the selected status
        $query = Ticket::where('status', $status)
            ->whereBetween($dateField, [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with(['assignedTo', 'creator', 'attachments']);

        // For regular users, only show tickets they created
        if ($role !== 'Admin') {
            $query->where('created_by', $user->id);
        }

        // Order by most recent relevant date
        $tickets = $query->latest($dateField)->get();

        // Check if PDF export is requested
        if ($request->has('export_pdf')) {
            return $this->exportToPdf($tickets, $startDate, $endDate, $reportTitle, $status);
        }

        return view('modules.tickets.reports', compact('role', 'tickets', 'startDate', 'endDate', 'status', 'reportTitle'));
    }

    /**
     * Export tickets to PDF
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $tickets
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  string  $reportTitle
     * @param  string  $status
     * @return \Illuminate\Http\Response
     */
    private function exportToPdf($tickets, $startDate, $endDate, $reportTitle, $status)
    {
        // Format dates for display
        $formattedStartDate = date('d M Y', strtotime($startDate));
        $formattedEndDate = date('d M Y', strtotime($endDate));

        $data = [
            'tickets' => $tickets,
            'startDate' => $formattedStartDate,
            'endDate' => $formattedEndDate,
            'reportTitle' => $reportTitle,
            'generatedAt' => now()->format('d M Y, h:i A')
        ];

        $pdf = Pdf::loadView('modules.tickets.pdf_report', $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        // Generate filename
        $filename = strtolower(str_replace(' ', '_', $status)) . '_tickets_report_' . $startDate . '_to_' . $endDate . '.pdf';

        // Return as download
        return $pdf->download($filename);
    }
}
