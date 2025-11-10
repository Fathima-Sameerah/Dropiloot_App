<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drop extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'category_id',
        'media',
        'campaign_type',
    ];

    protected $casts = [
        'media' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
