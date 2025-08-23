<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyAdmin extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'approve',
        'reason',
        'admin_id'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }


}
