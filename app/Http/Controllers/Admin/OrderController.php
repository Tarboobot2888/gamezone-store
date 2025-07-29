<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'service.game', 'gameAccount.game']);

        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%')
                               ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($orders);
        }

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'service.game', 'gameAccount.game', 'paymentTransactions']);

        if (request()->expectsJson()) {
            return response()->json($order);
        }

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled,refunded',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $oldStatus = $order->status;
        
        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        // Fire event for real-time notifications
        event(new OrderStatusChanged($order, $oldStatus));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Order status updated successfully'),
                'order' => $order->load(['user', 'service.game', 'gameAccount.game'])
            ]);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', __('Order status updated successfully'));
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'exists:orders,id',
            'status' => 'required|in:pending,processing,completed,cancelled,refunded'
        ]);

        $orders = Order::whereIn('id', $request->orders)->get();

        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $order->update(['status' => $request->status]);
            event(new OrderStatusChanged($order, $oldStatus));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Orders updated successfully')
            ]);
        }

        return redirect()->route('admin.orders.index')
            ->with('success', __('Orders updated successfully'));
    }

    public function export(Request $request)
    {
        $query = Order::with(['user', 'service.game', 'gameAccount.game']);

        // Apply same filters as index
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->get();

        $csvData = [];
        $csvData[] = [
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Product Type',
            'Product Name',
            'Game',
            'Amount',
            'Status',
            'Created At'
        ];

        foreach ($orders as $order) {
            $csvData[] = [
                $order->order_number,
                $order->user->name,
                $order->user->email,
                $order->service ? 'Service' : 'Account',
                $order->service ? $order->service->name : $order->gameAccount->title,
                $order->service ? $order->service->game->name : $order->gameAccount->game->name,
                '$' . number_format($order->total_amount, 2),
                ucfirst($order->status),
                $order->created_at->format('Y-m-d H:i:s')
            ];
        }

        $filename = 'orders_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function analytics(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $startDate = now()->subDays($period);

        $analytics = [
            'total_orders' => Order::where('created_at', '>=', $startDate)->count(),
            'total_revenue' => Order::where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->sum('total_amount'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->count(),
            'cancelled_orders' => Order::where('created_at', '>=', $startDate)
                ->where('status', 'cancelled')
                ->count(),
            'daily_sales' => Order::selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
                ->where('created_at', '>=', $startDate)
                ->where('status', 'completed')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'top_games' => Order::selectRaw('games.name, COUNT(*) as orders_count, SUM(orders.total_amount) as revenue')
                ->leftJoin('services', 'orders.service_id', '=', 'services.id')
                ->leftJoin('game_accounts', 'orders.game_account_id', '=', 'game_accounts.id')
                ->leftJoin('games', function($join) {
                    $join->on('services.game_id', '=', 'games.id')
                         ->orOn('game_accounts.game_id', '=', 'games.id');
                })
                ->where('orders.created_at', '>=', $startDate)
                ->where('orders.status', 'completed')
                ->groupBy('games.id', 'games.name')
                ->orderBy('orders_count', 'desc')
                ->limit(10)
                ->get()
        ];

        if ($request->expectsJson()) {
            return response()->json($analytics);
        }

        return view('admin.orders.analytics', compact('analytics'));
    }
}
