<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminServiceProviderServices extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'admin_id',
        'approve',
        'reason'
    ];

}
