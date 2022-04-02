<?php

namespace Ymg\EloquentFilter\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use Orchestra\Testbench\TestCase;
use Ymg\EloquentFilter\EloquentFilterServiceProvider;
use Ymg\EloquentFilter\FilterScope;
use Ymg\EloquentFilter\Tests\app\Models\Filters\FooFilter;
use Ymg\EloquentFilter\Tests\app\Models\Foo;

class EloquentFilterTest extends TestCase
{
    /**
     * @test
     */
    public function eloquent_should_apply_filter_scope()
    {
        $filterScope = Mockery::mock(FilterScope::class);
        $filterScope->shouldReceive('apply');
        $this->app->bind(FilterScope::class, fn() => $filterScope);

        $this->getJson('/foo');
    }

    /**
     * @test
     */
    public function eloquent_should_resolve_filter()
    {
        $filter = Mockery::mock(FooFilter::class);
        $filter->shouldReceive('setRequest');
        $filter->shouldReceive('resolve');
        $this->app->bind(FooFilter::class, fn() => $filter);

        $this->getJson('/foo');
    }

    /**
     * @test
     */
    public function eloquent_query_builder_should_filter_query()
    {
        $this->getJson('/foo?name=bar')->assertSee('name');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/foo', function (Request $request, Foo $foo) {
            return $foo->query()->toSql();
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.debug', true);
    }

    protected function getPackageProviders($app)
    {
        return [
            EloquentFilterServiceProvider::class
        ];
    }
}
