<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'availability_status_id'
    ];

    public function serviceProviderServices()
    {
        return $this->hasMany(ServiceProviderService::class);
    }

    public function reportOnService()
    {
        return $this->morphMany(ReportOnService::class, 'reportable');
    }

}
