<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyPopularityMetrics extends Model
{
    use HasFactory;

    protected $table = 'property_popularity_metrics_tabel'; 

    protected $fillable = [
        'property_id',
        'total_views',
        'total_favorites',
        // 'total_contacts', // Removed
        // 'view_to_contact_ratio', // Removed
        'last_updated',
    ];

    public $timestamps = false; 

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}