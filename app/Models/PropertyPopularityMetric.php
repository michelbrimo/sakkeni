<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyPopularityMetric extends Model
{
    use HasFactory;

    protected $table = 'property_popularity_metrics_tabel';
    protected $fillable = [
        'property_id',
        'total_views',
        'total_favorites',
        'total_contacts',
        'view_to_contact_ratio'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function updateRatio()
    {
        $this->view_to_contact_ratio = $this->total_views > 0 
            ? $this->total_contacts / $this->total_views 
            : 0;
        $this->save();
    }
}