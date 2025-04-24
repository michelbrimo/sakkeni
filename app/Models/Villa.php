<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Villa extends Model
{
    protected $fillable = [
        "residential_property_id",
        "floors",
    ];
    use HasFactory;
}
