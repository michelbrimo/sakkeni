<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_activity_id',
        'user_id',
        'service_provider_id',
        'rating',
        'review_text',
    ];

    public function serviceActivity()
    {
        return $this->belongsTo(ServiceActivity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
