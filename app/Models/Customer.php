<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    protected $table = 'customers';

    use HasApiTokens, Notifiable;
    // use HasApiTokens;
    public $timestamps = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'remember_token',
        'phone',
        'avatar_2d',
        'rank',
        'money',
        'status',
        'email_verified_at',
        'confirmation_code',
    ];

    protected $hidden = [
        'password',
    ];
}
