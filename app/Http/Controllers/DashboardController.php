<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        $recentOrders = Order::where('user_id', $user->id)
            ->with(['service.game', 'gameAccount.game'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $orderStats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'completed' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
        ];

        $openTickets = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        return view('dashboard.index', compact('recentOrders', 'orderStats', 'openTickets'));
    }

    public function orders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['service.game', 'gameAccount.game'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.orders', compact('orders'));
    }

    public function accounts()
    {
        $purchasedAccounts = Order::where('user_id', Auth::id())
            ->where('product_type', 'account')
            ->where('status', 'completed')
            ->with('gameAccount.game')
            ->orderBy('completed_at', 'desc')
            ->paginate(10);

        return view('dashboard.accounts', compact('purchasedAccounts'));
    }

    public function profile()
    {
        return view('dashboard.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'preferred_language' => 'required|in:en,ar',
            'theme_preference' => 'required|in:light,dark',
        ]);

        $user->update($request->only(['name', 'phone', 'preferred_language', 'theme_preference']));

        return response()->json([
            'success' => true,
            'message' => __('Profile updated successfully')
        ]);
    }

    public function supportTickets()
    {
        $tickets = SupportTicket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.support-tickets', compact('tickets'));
    }
}
