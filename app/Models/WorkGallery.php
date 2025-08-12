<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_service_id',
        'image_path'
    ];
}
