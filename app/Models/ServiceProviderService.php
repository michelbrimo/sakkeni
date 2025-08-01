<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderService extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'service_provider_id',
        'availability_status_id',
        'service_id'
    ];

    public function gallery()
    {
        return $this->hasMany(WorkGallery::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

}
