<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertySimilarity extends Model
{
    use HasFactory;

    protected $table = 'property_similarity_scores_tabel';
    protected $primaryKey = ['property_id_1', 'property_id_2'];
    public $incrementing = false;
    protected $fillable = [
        'property_id_1', 
        'property_id_2',
        'overall_similarity',
        'price_similarity',
        'feature_similarity',
        'location_similarity'
    ];

    public function property1()
    {
        return $this->belongsTo(Property::class, 'property_id_1');
    }

    public function property2()
    {
        return $this->belongsTo(Property::class, 'property_id_2');
    }
}
