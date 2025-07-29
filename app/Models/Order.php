<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'user_id',
        'order_number',
        'product_type',
        'product_id',
        'service_id',
        'game_account_id',
        'quantity',
        'unit_price',
        'total_amount',
        'currency',
        'status',
        'payment_method',
        'payment_id',
        'payment_status',
        'customer_details',
        'processing_details',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'customer_details' => 'array',
        'processing_details' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function gameAccount()
    {
        return $this->belongsTo(GameAccount::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REFUNDED => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 2) . ' ' . strtoupper($this->currency);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
