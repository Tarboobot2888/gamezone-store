<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_method',
        'payment_id',
        'amount',
        'currency',
        'status',
        'payment_data',
        'refund_data',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_data' => 'array',
        'refund_data' => 'array',
        'processed_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_REFUNDED => 'dark',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
