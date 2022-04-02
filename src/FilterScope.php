<?php

namespace Ymg\EloquentFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Throwable;

class FilterScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = ['WithoutFilter'];

    /**
     * @throws Throwable
     */
    public function apply(Builder $builder, Model $model)
    {
        $model_class = str_replace('\\', '/', get_class($model));
        $model_path = dirname($model_class);
        $filter_name = basename($model_class) . 'Filter';

        $filter_class = (config('eloquent-filter.filters_path') ?: 'App\\Models\\Filters\\') . $filter_name;

        if (!class_exists($filter_class)) {
            $filter_class = str_replace('/', '\\', $model_path . '/Filters/' . $filter_name);
        }

        if (class_exists($filter_class)) {
            /** @var EloquentFilter $filter */
            $filter = app($filter_class);
            $filter->setRequest(request());
            $filter->resolve($builder);
        }
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    protected function addWithoutFilter(Builder $builder)
    {
        $builder->macro('withoutFilter', function (Builder $builder) {
            $builder->withoutGlobalScope(self::class);
        });
    }
}
