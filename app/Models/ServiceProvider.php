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
        'description',
        'rate',
        'num_of_rating',
    ];

    public function serviceProviderServices()
    {
        return $this->hasMany(ServiceProviderService::class)->where('availability_status_id', AvailabilityStatus::Active);
    }

    public function serviceProviderPendingServices()
    {
        return $this->hasMany(ServiceProviderService::class)->where('availability_status_id', AvailabilityStatus::Pending);
    }

    public function reportOnService()
    {
        return $this->morphMany(ReportOnService::class, 'reportable');
    }
public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
