<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description'
    ];

    public function serviceProviderServices()
    {
        return $this->hasMany(ServiceProviderService::class)->where('availability_status_id', AvailabilityStatus::Active);
    }

    public function servicePendingProviderServices()
    {
        return $this->hasMany(ServiceProviderService::class)->where('availability_status_id', AvailabilityStatus::Pending);
    }

    public function reportOnService()
    {
        return $this->morphMany(ReportOnService::class, 'reportable');
    }

}
