<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Models\Contracts\RoleAware;
use App\Models\Traits\HasJWT;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Mitoop\LaravelSnowflake\HasSnowflakeIds;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, RoleAware
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasJWT, HasSnowflakeIds, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function emailVerified(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, $attribute) => ! empty($attribute['email']),
        );
    }

    protected function phoneVerified(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, $attribute) => ! empty($attribute['phone_verified_at']),
        );
    }

    protected function isPasswordSet(): Attribute
    {
        return Attribute::make(
            get: fn () => ! empty($this->password),
        );
    }

    public function role(): Role
    {
        return Role::USER;
    }
}
