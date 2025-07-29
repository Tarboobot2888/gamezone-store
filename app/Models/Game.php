<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'banner_image',
        'category',
        'platform',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function accounts()
    {
        return $this->hasMany(GameAccount::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('images/default-game.svg');
    }

    public function getBannerUrlAttribute()
    {
        return $this->banner_image ? asset('storage/' . $this->banner_image) : asset('images/default-banner.svg');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
