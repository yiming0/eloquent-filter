<?php

namespace Ymg\EloquentFilter;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EloquentFilterServiceProvider extends ServiceProvider
{
    /**
     * Register Eloquent-Filter services.
     *
     * @return void
     * @throws BindingResolutionException
     */
    public function register()
    {
        if (!$this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/eloquent-filter.php', 'eloquent-filter');
        }
    }

    /**
     * Bootstrap Eloquent-Filter services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/eloquent-filter.php' => config_path('eloquent-filter.php'),
            ], 'eloquent-filter-config');
        }

        $this->addEloquentGlobalScope();
    }

    private function addEloquentGlobalScope()
    {
        $config = $this->app->make('config')->get('eloquent-filter');

        if ($config['auto_register']) {
            Event::listen('eloquent.booted: *', function ($event, $models) {
                /** @var Model[] $models */
                foreach ($models as $model) {
                    $model::addGlobalScope(app(FilterScope::class));
                }
            });
        }
    }
}
