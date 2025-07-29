<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    use HasFactory;

    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_OPEN => 'primary',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_RESOLVED => 'success',
            self::STATUS_CLOSED => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            self::PRIORITY_LOW => 'success',
            self::PRIORITY_MEDIUM => 'warning',
            self::PRIORITY_HIGH => 'danger',
            self::PRIORITY_URGENT => 'dark',
        ];

        return $badges[$this->priority] ?? 'secondary';
    }
}
