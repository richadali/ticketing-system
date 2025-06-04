<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role ? $user->role->name : 'User';

        // Ticket statistics
        $totalTickets = Ticket::count();
        $openTickets = Ticket::where('status', 'open')->count();
        $inProgressTickets = Ticket::where('status', 'in_progress')->count();
        $closedTickets = Ticket::where('status', 'closed')->count();

        // Get ticket trend data for the last 14 days
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(13); // 14 days including today

        // Format dates for chart
        $dateRange = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->format('M d');
            $currentDate->addDay();
        }

        // Get created tickets per day
        $createdTickets = $this->getTicketsCountByDateRange($startDate, $endDate, 'created_at');

        // Get closed tickets per day
        $closedTicketsData = $this->getTicketsCountByDateRange($startDate, $endDate, 'updated_at', 'closed');

        // Get tickets by assigned user (for admins only)
        $ticketsByUser = [];
        $userNames = [];
        $ticketAgeCategories = [];
        $ticketAgeCounts = [];

        if ($role === 'Admin') {
            $ticketAssignmentData = Ticket::select('users.name', DB::raw('count(*) as ticket_count'))
                ->leftJoin('users', 'tickets.assigned_to', '=', 'users.id')
                ->groupBy('tickets.assigned_to', 'users.name')
                ->orderBy('ticket_count', 'desc')
                ->limit(10)
                ->get();

            foreach ($ticketAssignmentData as $item) {
                $userNames[] = $item->name ?? 'Unassigned';
                $ticketsByUser[] = $item->ticket_count;
            }

            // Get data for ticket age chart (only non-closed tickets)
            $now = Carbon::now();

            // Tickets less than 1 day old
            $ticketsUnder1Day = Ticket::where('status', '!=', 'closed')
                ->where('created_at', '>=', $now->copy()->subDay())
                ->count();

            // Tickets 1-3 days old
            $tickets1To3Days = Ticket::where('status', '!=', 'closed')
                ->where('created_at', '<', $now->copy()->subDay())
                ->where('created_at', '>=', $now->copy()->subDays(3))
                ->count();

            // Tickets 3-7 days old
            $tickets3To7Days = Ticket::where('status', '!=', 'closed')
                ->where('created_at', '<', $now->copy()->subDays(3))
                ->where('created_at', '>=', $now->copy()->subDays(7))
                ->count();

            // Tickets over 7 days old
            $ticketsOver7Days = Ticket::where('status', '!=', 'closed')
                ->where('created_at', '<', $now->copy()->subDays(7))
                ->count();

            $ticketAgeCategories = ['< 1 day', '1-3 days', '3-7 days', '> 7 days'];
            $ticketAgeCounts = [$ticketsUnder1Day, $tickets1To3Days, $tickets3To7Days, $ticketsOver7Days];
        }

        // Store the role in the session
        session()->put('role', $role);

        // Pass the data to the view
        return view('home')->with(compact(
            'role',
            'totalTickets',
            'openTickets',
            'inProgressTickets',
            'closedTickets',
            'dateRange',
            'createdTickets',
            'closedTicketsData',
            'ticketsByUser',
            'userNames',
            'ticketAgeCategories',
            'ticketAgeCounts'
        ));
    }

    /**
     * Get ticket count by date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $dateField
     * @param string|null $status
     * @return array
     */
    private function getTicketsCountByDateRange($startDate, $endDate, $dateField, $status = null)
    {
        $query = Ticket::select(
            DB::raw("DATE($dateField) as date"),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween($dateField, [$startDate, $endDate->endOfDay()]);

        if ($status) {
            $query->where('status', $status);
        }

        $results = $query->groupBy(DB::raw("DATE($dateField)"))
            ->get()
            ->keyBy('date');

        // Fill in gaps with zeros
        $data = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $data[] = isset($results[$dateKey]) ? $results[$dateKey]->count : 0;
            $currentDate->addDay();
        }

        return $data;
    }
}
