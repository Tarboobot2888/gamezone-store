<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $newStatus;
    public $previousStatus;

    public function __construct(Order $order, $newStatus, $previousStatus = null)
    {
        $this->order = $order;
        $this->newStatus = $newStatus;
        $this->previousStatus = $previousStatus;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->order->user_id),
            new PrivateChannel('admin.orders'),
        ];
    }

    public function broadcastAs()
    {
        return 'order.status.changed';
    }

    public function broadcastWith()
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'new_status' => $this->newStatus,
            'previous_status' => $this->previousStatus,
            'message' => $this->getStatusMessage(),
            'timestamp' => now()->toISOString(),
        ];
    }

    private function getStatusMessage()
    {
        $messages = [
            'pending' => 'Your order is pending payment.',
            'processing' => 'Your order is being processed.',
            'completed' => 'Your order has been completed successfully!',
            'cancelled' => 'Your order has been cancelled.',
            'refunded' => 'Your order has been refunded.',
            'disputed' => 'Your order is under dispute review.',
        ];

        return $messages[$this->newStatus] ?? 'Your order status has been updated.';
    }
}
