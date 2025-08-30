<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'amount',
        'status',
        'payment_gateway_transaction_id',
    ];

    /**
     * Get the property that this payment belongs to.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
