<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'admin_id',
        'approve',
        'reason'
    ];
}
