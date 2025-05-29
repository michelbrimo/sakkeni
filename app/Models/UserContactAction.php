<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContactAction extends Model
{
    use HasFactory;

    protected $table = 'user_contact_actions_tabel';
    protected $fillable = [
        'user_id',
        'property_id',
        'action_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}