<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'subscribed_at',
        'unsubscribed_at',
        'is_active',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('unsubscribed_at');
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe()
    {
        $this->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }
}
