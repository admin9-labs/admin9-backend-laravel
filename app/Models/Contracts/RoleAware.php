<?php

namespace App\Models\Contracts;

use App\Enums\Role;

interface RoleAware
{
    public function role(): Role;
}
