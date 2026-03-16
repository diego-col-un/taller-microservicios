<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    // Requerido por JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // Requerido por JWT
    public function getJWTCustomClaims()
    {
        return [];
    }
}