<?php

namespace Ymg\EloquentFilter\Tests\app\Models\Filters;

use Ymg\EloquentFilter\EloquentFilter;

class FooFilter extends EloquentFilter
{
    public function filterName($value)
    {
        $this->builder->where('name', $value);
    }
}
