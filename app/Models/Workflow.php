<?php

namespace App\Models;

use App\Models\Traits\HasModelDefaults;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasModelDefaults, HasUlids;

    protected $fillable = [
        'name',
        'content',
    ];
}
