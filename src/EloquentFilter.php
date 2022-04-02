<?php

namespace Ymg\EloquentFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class EloquentFilter
{
    /**
     * @var Builder
     */
    protected Builder $builder;
    /**
     * @var Request
     */
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public static function make(Request $request)
    {
        return new static($request);
    }

    /**
     * @param Request $request
     * @return EloquentFilter
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return void
     */
    public function resolve(Builder $builder)
    {
        $this->builder = $builder;
        foreach (array_filter($this->request->all()) as $filter => $value) {
            if (method_exists($this, $method_name = 'filter' . Str::ucfirst(Str::snake($filter)))) {
                call_user_func_array([$this, $method_name], Arr::wrap($value));
            }
        }
    }
}
