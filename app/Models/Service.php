<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'game_id',
        'name',
        'description',
        'price',
        'currency',
        'package_type',
        'package_value',
        'image',
        'is_active',
        'is_featured',
        'sort_order',
        'processing_time',
        'requirements',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'requirements' => 'array',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/default-service.svg');
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . strtoupper($this->currency);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
