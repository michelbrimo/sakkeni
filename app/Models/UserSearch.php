<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSearch extends Model
{
   use HasFactory;

    protected $table = 'user_search_tabel';
    protected $fillable = [
        'user_id',
        'filters'
    ];

    protected $casts = [
        'filters' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}