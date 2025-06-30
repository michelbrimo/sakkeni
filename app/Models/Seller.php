<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{

    protected $fillable = [
        'user_id',
        'account_type_id',
        'free_ads_left'
    ];
    
    use HasFactory;

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
}
