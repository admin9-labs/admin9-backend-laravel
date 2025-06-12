<?php

namespace App\Models\Traits;

use Hashids;

trait HasJWT
{
    public function getJWTIdentifier()
    {
        if ($this->getKeyType() === 'int') {
            return Hashids::connection('jwt')->encode($this->getKey());
        }

        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'iss' => 'admin9',
        ];
    }
}
