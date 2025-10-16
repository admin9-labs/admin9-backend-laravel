<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Models\Contracts\RoleAware;
use App\Models\Traits\HasJWT;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, RoleAware
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasJWT, HasUlids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'nickname',
        'introduction',
        'avatar',
        // 'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function emailVerified(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, $attribute) => ! empty($attribute['email']),
        );
    }

    protected function mobileVerified(): Attribute
    {
        return Attribute::make(
            get: fn () => ! empty($this->mobile),
        );
    }

    protected function identityVerified(): Attribute
    {
        return Attribute::make(
            get: fn () => ! empty($this->identity_verified_at),
        );
    }

    public function role(): Role
    {
        return Role::USER;
    }
}
