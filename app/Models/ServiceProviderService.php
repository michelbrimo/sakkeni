<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProviderService extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'service_provider_id',
        'service_id'
    ];

    public function gallery()
    {
        return $this->hasMany(WorkGallery::class);
    }

}
