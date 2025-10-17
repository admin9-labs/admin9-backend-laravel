<?php

namespace App\Models;

use App\Enums\Role;
use App\Models\Contracts\RoleAware;
use App\Models\Traits\HasJWT;
use Database\Factories\AdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Admin extends Authenticatable implements JWTSubject, RoleAware
{
    /** @use HasFactory<AdminFactory> */
    use HasFactory, HasJWT, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): Role
    {
        return Role::ADMIN;
    }
}
