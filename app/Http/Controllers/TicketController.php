<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Apply filters if provided
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
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
        $statuses = ['all' => 'All', 'open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];

        // Admin-specific filters
        $adminFilters = [
            'all' => 'All Tickets',
            'assigned_to_me' => 'Assigned to Me',
            'unassigned' => 'Unassigned',
            'created_by_me' => 'Created by Me'
        ];

        return view('modules.tickets.index', compact('role', 'tickets', 'statuses', 'adminFilters'));
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
            'attachments.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $ticket = new Ticket();
        $ticket->name = $request->name;
        $ticket->description = $request->description;
        $ticket->status = 'open';
        $ticket->created_by = Auth::id();
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
        return view('modules.tickets.show', compact('ticket', 'role'));
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
        ]);

        // Regular users can only edit name and description, not status or assignment
        if ($role !== 'Admin') {
            // Track changes to name and description
            if ($ticket->name != $request->name || $ticket->description != $request->description) {
                $this->recordActivity(
                    $ticket,
                    'updated',
                    'Ticket details were updated'
                );
            }

            // Update only name and description
            $ticket->name = $request->name;
            $ticket->description = $request->description;
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
}
