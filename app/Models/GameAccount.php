<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'game_id',
        'title',
        'description',
        'price',
        'currency',
        'platform',
        'region',
        'level',
        'rank',
        'images',
        'specifications',
        'login_credentials',
        'is_available',
        'is_featured',
        'stock_quantity',
        'sold_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'images' => 'array',
        'specifications' => 'array',
        'login_credentials' => 'encrypted:array',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'product_id')->where('product_type', 'account');
    }

    public function getMainImageAttribute()
    {
        $images = $this->images ?? [];
        return !empty($images) ? asset('storage/' . $images[0]) : asset('images/default-account.svg');
    }

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . strtoupper($this->currency);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('stock_quantity', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
