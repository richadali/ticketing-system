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

        // Get tickets sorted by deadline and urgent status
        $tickets = $query->orderBy('deadline', 'asc')  // First by deadline (ascending)
            ->orderBy('urgent', 'desc')   // Then urgent tickets first within each deadline
            ->get();

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
        $ticket->load('assignedTo', 'attachments', 'activities.user', 'creator', 'comments.user');

        $adminUsers = [];
        if ($role === 'Admin') {
            $adminUsers = User::whereHas('role', function ($query) {
                $query->where('name', 'Admin');
            })->get();
        }

        return view('modules.tickets.edit', compact('ticket', 'role', 'adminUsers'));
    }

    /**
     * Display the specified resource for modal view.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function details(Ticket $ticket)
    {
        $ticket->load('assignedTo', 'attachments', 'activities.user', 'creator', 'comments.user');

        return view('modules.tickets.details', compact('ticket'));
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
            return redirect()->route('tickets.edit', $ticket)->with('error', 'You do not have permission to edit this ticket.');
        }

        // // Only open tickets can be edited
        // if ($ticket->status !== 'open') {
        //     return redirect()->route('tickets.show', $ticket)->with('error', 'Only tickets with "Open" status can be edited.');
        // }

        $users = User::whereHas('role', function ($query) {
            $query->where('name', 'Admin');
        })->get();

        return view('modules.tickets.edit', compact('ticket', 'role', 'isCreator', 'users'));
    }

    /**
     * Update the status and assignment of a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function updateStatusAndAssignment(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Authorization: Only admins can perform this action.
        if ($role !== 'Admin') {
            return redirect()->route('tickets.edit', $ticket)->with('error', 'You do not have permission to perform this action.');
        }

        $request->validate([
            'status' => 'sometimes|required|in:open,in_progress,closed',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ]);

        // Track status change
        if ($request->has('status') && $ticket->status != $request->status) {
            $this->recordActivity(
                $ticket,
                'status_changed',
                'Ticket status changed to ' . $request->status,
                $ticket->status,
                $request->status
            );
            $ticket->status = $request->status;
            if ($request->status == 'closed') {
                $ticket->closed_at = now();
            } else {
                $ticket->closed_at = null;
            }
        }

        // Track assignment change
        if ($request->has('assigned_to') && $ticket->assigned_to != $request->assigned_to) {
            $newAssigneeName = 'Unassigned';
            if ($request->assigned_to) {
                $newAssignee = User::find($request->assigned_to);
                $newAssigneeName = $newAssignee->name;
            }
            $this->recordActivity(
                $ticket,
                'assigned',
                'Ticket was assigned to ' . $newAssigneeName
            );
            $ticket->assigned_to = $request->assigned_to;
        }

        $ticket->save();

        return redirect()->route('tickets.edit', $ticket)->with('success', 'Ticket updated successfully.');
    }

    /**
     * Update the details of a specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function updateDetails(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';
        $isCreator = $ticket->created_by == $user->id;

        // Authorization: Only admins or the ticket creator can update details.
        if ($role !== 'Admin' && !$isCreator) {
            return redirect()->route('tickets.edit', $ticket)->with('error', 'You do not have permission to update this ticket.');
        }

        // // Business Logic: Only tickets with "Open" status can be edited.
        // if ($ticket->status !== 'open') {
        //     return redirect()->route('tickets.show', $ticket)->with('error', 'Only tickets with "Open" status can be edited.');
        // }

        // Validation for ticket details
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'category' => 'required|string|in:Whitelabel,Reports,Website,Email,Domain,Others',
            'sub_company' => 'required|string|in:CG,Teesprint',
            'urgent' => 'nullable|boolean',
        ]);

        // Track changes for the activity log
        if (
            $ticket->name != $request->name ||
            $ticket->description != $request->description ||
            $ticket->deadline != $request->deadline ||
            $ticket->category != $request->category ||
            $ticket->sub_company != $request->sub_company ||
            $ticket->urgent != $request->has('urgent')
        ) {
            $this->recordActivity(
                $ticket,
                'updated',
                'Ticket details were updated'
            );
        }

        // Update ticket details
        $ticket->name = $request->name;
        $ticket->description = $request->description;
        $ticket->deadline = $request->deadline;
        $ticket->category = $request->category;
        $ticket->sub_company = $request->sub_company;
        $ticket->urgent = $request->has('urgent');
        $ticket->save();

        return redirect()->route('tickets.edit', $ticket)->with('success', 'Ticket details updated successfully.');
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
            return redirect()->route('tickets.edit', $ticket)->with('error', 'You do not have permission to assign tickets.');
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

        return redirect()->route('tickets.edit', $ticket)->with('success', 'Ticket assigned to you successfully and set to In Progress.');
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
            return redirect()->route('tickets.edit', $ticket)->with('error', 'You do not have permission to change ticket status.');
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

        return redirect()->route('tickets.edit', $ticket)->with('success', 'Ticket status updated successfully.');
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
            'assigned_to' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Check permissions
        if ($role !== 'Admin') {
            return redirect()->route('tickets.edit', $ticket)
                ->with('error', 'Only admins can assign tickets.');
        }

        // Update ticket
        $oldAssignee = $ticket->assigned_to;
        $ticket->assigned_to = $request->assigned_to;
        $ticket->save();

        // Record activity
        $newAdmin = User::find($request->assigned_to);
        $oldAdmin = $oldAssignee ? User::find($oldAssignee) : null;

        $this->recordActivity(
            $ticket,
            'assigned',
            'Ticket was assigned to ' . $newAdmin->name,
            $oldAdmin ? $oldAdmin->name : 'Unassigned',
            $newAdmin->name
        );

        return redirect()->route('tickets.edit', $ticket)
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

        // Get status from request, maintain it in session if valid
        $validStatuses = ['open', 'in_progress', 'closed'];
        $requestStatus = $request->input('status');

        if ($requestStatus && in_array($requestStatus, $validStatuses)) {
            // Valid status provided in request, use it and store in session
            $status = $requestStatus;
            session(['report_status' => $status]);
        } else if (session('report_status') && in_array(session('report_status'), $validStatuses)) {
            // No valid status in request, but we have one in session
            $status = session('report_status');
        } else {
            // Default to open if no valid status in request or session
            $status = 'open';
            session(['report_status' => $status]);
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

        // Order by deadline and urgent status
        if ($status == 'closed') {
            // For closed tickets, sort by closed date
            $tickets = $query->orderBy($dateField, 'desc')
                ->orderBy('urgent', 'desc')
                ->get();
        } else {
            // For open/in-progress tickets, sort by deadline then urgent
            $tickets = $query->orderBy('deadline', 'asc')    // First by deadline (ascending)
                ->orderBy('urgent', 'desc')     // Then urgent tickets first within each deadline
                ->get();
        }

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
            'generatedAt' => now()->format('d M Y, h:i A'),
            'status' => $status
        ];

        $pdf = Pdf::loadView('modules.tickets.pdf_report', $data);

        // Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        // Generate filename
        $filename = strtolower(str_replace(' ', '_', $status)) . '_tickets_report_' . $startDate . '_to_' . $endDate . '.pdf';

        // Return as download
        return $pdf->download($filename);
    }
    /**
     * Add a comment to a ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function addComment(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Only admins can add comments
        if ($role !== 'Admin') {
            return redirect()->route('tickets.edit', $ticket)->with('error', 'You do not have permission to add comments.');
        }

        $request->validate([
            'comment' => 'required|string',
        ]);

        $ticket->comments()->create([
            'user_id' => $user->id,
            'body' => $request->comment,
        ]);

        return redirect()->route('tickets.edit', $ticket)->with('success', 'Comment added successfully.');
    }
}
