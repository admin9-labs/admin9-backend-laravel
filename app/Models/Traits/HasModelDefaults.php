<?php

namespace App\Models\Traits;

use DateTimeInterface;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @mixin Eloquent
 * @mixin Builder
 * @mixin QueryBuilder
 */
trait HasModelDefaults
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getPerPage()
    {
        return min(request('page_size', 15), 100);
    }
}
