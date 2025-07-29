<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Game;
use App\Models\Service;
use App\Models\GameAccount;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super-admin|admin']);
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'open_tickets' => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
        ];

        // Daily sales for the last 30 days
        $dailySales = Order::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top games by orders
        $topGames = Game::withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get();

        // Recent orders
        $recentOrders = Order::with(['user', 'service.game', 'gameAccount.game'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'dailySales', 'topGames', 'recentOrders'));
    }

    public function users()
    {
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function games()
    {
        $games = Game::withCount(['services', 'accounts'])
            ->orderBy('sort_order')
            ->paginate(20);

        return view('admin.games.index', compact('games'));
    }

    public function services()
    {
        $services = Service::with('game')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.services.index', compact('services'));
    }

    public function accounts()
    {
        $accounts = GameAccount::with('game')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.accounts.index', compact('accounts'));
    }

    public function orders()
    {
        $orders = Order::with(['user', 'service.game', 'gameAccount.game'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function supportTickets()
    {
        $tickets = SupportTicket::with(['user', 'assignedTo'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.support-tickets.index', compact('tickets'));
    }

    public function settings()
    {
        return view('admin.settings');
    }
}
