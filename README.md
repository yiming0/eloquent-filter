# eloquent-filter

### 使用

- 安装

```
composer config repo.eloquent-filter vcs "https://github.com/yiming0/eloquent-filter.git"
composer require ymg/eloquent-filter
```

- 发布资源

```
php artian vendor:publish --tag=eloquent-filter-config
```

- 定义过滤器

```php
namespace App\Models\Filters;

/**
 * User Model Filter
 */
class UserFilter extends \Ymg\EloquentFilter\EloquentFilter 
{
    /**
    * User.name 字段过滤方法
    * @param $value
    * @return void
    */
    public function filterName($value)
    {
        $this->builder->where('name', $value);
    }

}
```

### 配置

- 参考 `config/eloquent-filter.php`

### 说明

1. 默认已开启过滤器自动注册，可以修改配置文件：`"auto_register" => false`。

2. 使用效果：
  - 路由：
```php
Route::get('user', function(App\Models\User $user){
    return $user->toSql();
});
```
  - 请求：
```http request
GET /user?name=test
```
  - 结果：
```sql
select * from users where "name" = "test";
```

3. 如果关闭了过滤器自动注册，可以为模型手动应用过滤器： 
  - 全局 Scope:
```php
//  App\Models\User

use Ymg\EloquentFilter\FilterScope;

// ...

    protected static function booted() 
    {
        parent::booted();
        static::addGlobalScope(app(FilterScope::class));
    }

// ...
```
  - 局部 Scope:
```php
//  App\Models\User

use App\Models\Filters\UserFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Ymg\EloquentFilter\EloquentFilter;

// ...
    
    public function scopeApplyFilter(Builder $builder, EloquentFilter $filter) 
    {
        $filter->resolve($builder)
    }

    public function scopeFiltered(Builder $builder, Request $request) 
    {
        UserFilter::make($request)->resolve($builder)
    }

```
  - 局部 Scope 使用：
```php
// App\Http\Controllers\UserController

//...

    public function index(Request $request, User $user)
    {
        return $user->filtered($request)->get();
    }
    
    public function store(Request $request, User $user)
    {
        $request->merge(['name' => $request->input('username')]);
    
        return $user->filtered($request)->get();
    }
```
```php
// App\Http\Controllers\UserController

//...

    public function index(Request $request, User $user)
    {
        return $user->applyFilter(UserFilter::make($request))->get();
    }
```

> 关于Scope，参考Laravel文档 [#query-scopes](https://laravel.com/docs/eloquent#query-scopes)

4. 通用过滤器
  - 定义
```php
namespace App\Models\Filters;

use Ymg\EloquentFilter\EloquentFilter;

class StatusFilter extends EloquentFilter
{
    public function filterStatus($value)
    {
        $this->builder->where('status', $value);
    }
}
```
  - 使用
```php
// App\Http\Controllers\UserController

use App\Models\Filters\StatusFilter;

//...

    public function index(User $user, StatusFilter $filter)
    {
        return $user->applyFilter($filter)->get();
    }

```
