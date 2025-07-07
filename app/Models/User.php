<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        "profile_picture_path",
        "address",
        "phone_number",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function preferences()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function favorites()
    {
        return $this->hasMany(PropertyFavorite::class);
    }

    public function searches()
    {
        return $this->hasMany(UserSearch::class);
    }

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function serviceProvider()
    {
        return $this->hasOne(serviceProvider::class);
    }


}
