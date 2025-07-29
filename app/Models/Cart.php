<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_type',
        'product_id',
        'quantity',
        'unit_price',
        'customer_details',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'customer_details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'product_id')->where('product_type', 'service');
    }

    public function gameAccount()
    {
        return $this->belongsTo(GameAccount::class, 'product_id')->where('product_type', 'account');
    }

    public function getProductAttribute()
    {
        if ($this->product_type === 'service') {
            return $this->service;
        }
        return $this->gameAccount;
    }

    public function getTotalPriceAttribute()
    {
        return $this->unit_price * $this->quantity;
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_price, 2) . ' USD';
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}
