<?php

namespace App\Support\Auth;

use Hashids;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\EloquentUserProvider as BaseEloquentUserProvider;
use Illuminate\Database\Eloquent\Model;

class EloquentUserProvider extends BaseEloquentUserProvider
{
    public function retrieveById($identifier)
    {
        $model = $this->createModel();
        if ($model->getKeyType() === 'int') {
            $identifier = Hashids::connection('jwt')->decode($identifier);
        }

        /** @var Model&Authenticatable $model */
        return $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
    }
}
