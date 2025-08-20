<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use App\Models\AvailabilityStatus as ModelsAvailabilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderService extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'service_provider_id',
        'availability_status_id',
        'description',
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

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function availabilityStatus()
    {
        return $this->belongsTo(ModelsAvailabilityStatus::class);
    }


}
