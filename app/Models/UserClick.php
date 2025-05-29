<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClick extends Model
{
    use HasFactory;

    protected $table = 'user_clicks_tabel';
    protected $fillable = [
        'user_id',
        'property_id',
        'click_count',
        'session_duration'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function incrementClick()
    {
        $this->increment('click_count');
        $this->save();
    }
}