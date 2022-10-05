# components

## mpie/config

This is a component package that can be used independently, you can use dot syntax to get the loaded configuration

### Install

```shell
composer require mpie/config
```

### use

```php
$repository = new \Mpie\Config\Repository();

// Scan all PHP files in this path
$repository->scan([__DIR__]);

// load a file
$repository->load([__DIR__.'./config/app.php']);

// get configuration
$repository->get('app.debug');
```

> Note: The $repository example should be kept as a singleton to avoid repeated loading. The rules for loading configuration are as follows

For example, the content of the app.php configuration file is as follows

```php
return [
     'debug' => true,
];
```

After loading, the file name will be used as the key of the outer array, so to get the configuration, you need to use `$repository->get('app.debug'')`, which supports the use of dot syntax.

## mpie/di

A simple container that can be used independently

### Install

```shell
composer require mpie/di
```

### use

Get the container instance, be careful not to instantiate it directly

```php
$container = \Mpie\Di\Context::getContainer();
$container = container();
```

Binding classes and aliases

```php
$container->bind(TestInterface::class, Test::class);
```

All container interfaces can then use the TestInterface::class identifier to obtain Test::class instances

Instantiate the Test class

```php
$container->make(Test::class);
```

get object

```php
$container->get(Test::class);
```

call method

```php
$conatiner->call(callable $callable, array $arguments = []);
```

> Note: All APIs that need to pass parameters require an associative array, and the key of the array is the name of the parameter. All classes instantiated by the container and all dependencies are singletons

## mpie/routing

### Initialize

```php
$router = new Router(array $options = [], ?Mpie\Routing\RouteCollector $routeCollector);
```

If the route collector passed to the Router class is null, it will be automatically instantiated internally

### use

#### Support GET, POST, PUT, PATCH, DELETE and other methods, for example:

```
$router->get('/', function() {
    // Do something.
});
```

If you need a route to register a custom request method, for example:

```
$router->request('/', function() {
    // Do something.
}, ['GET', 'OPTION']);
```

If you need a route to support all request methods, for example:

```
$router->any('/', function() {
    // Do something.
});
```

#### If you need to register a Restful route, for example:

```
$router->rest('/book', 'BookController');
```

Restful rules will register multiple routes. The routes registered by the above rules are as follows:

| Methods | Uri | Action |
| --- | --- | --- |
| GET /HEAD | /book | BookController@index |
| GET /HEAD | /book/{id} | BookController@show |
| POST | /book | BookController@store |
| PUT/PATCH | /book/{id} | BookController@update |
| DELETE | /book/{id} | BookController@delete |

It is worth noting that the rest method returns a RestRouter object, through which you can get the routing rules corresponding to an action and register other parameters, for example:

```php
$rest = $router->rest('/book', 'BookController');
$rest->getShowRoute()->middleware('JWTAuthentication');
```

#### routes support parameters, for example:

```php
// With parameter type restrictions, the id will only match if it is a number
$router->get('/book/{id:\d+}', 'BookController@show');
// Routes with suffixes, pay attention to the symbols here. Will be parsed as part of regular metacharacters, so it is necessary to add backslash escapes
$router->get('/p/{id}\.html', 'CateController@show');
```

#### Routes support grouping and support grouping nesting, for example:

```php
$router->prefix('api')->group(function(\Mpie\Routing\Router $router) {
    $router->middleware('Authentication')->group(function(\Mpie\Routing\Router $router) {
        $router->get('/', function(\Mpie\Routing\Router $router) {
            // Do something.
        });
        
        $router->where('id', '\d+')->get('/user/{id}', 'UserController@show');
    });
```

The above rule defines two routing rules, the first request method is GET, the path is the routing rule of `/api`, and the middleware contains `Authentication`
, the second item also has parameter type restrictions compared to the first item. At this time, the id parameter can only be a number

> The packet routing prefix method supports `prefix`, `namespace`, `middleware`, `where`, etc.

For packet routing, you can also register by importing files in the closure, for example:

```php
$router->group(function(\Mpie\Routing\Router $router) {
    // use the method of importing files
    require_once './route.php';
});
```

Routes in the file are registered using $router

#### parsing routes

Resolving routes is done using route collectors. If you do not use external, you can use the methods provided by the Router object to obtain

```php
$routeCollector = $router->getRouteCollector();
```

There are two methods of parsing

```php
$route = $routeCollector->resolve('GET', '/'); // Pass the request method and path
$route = $routeCollector->resolveRequest($request); // Pass a Psr\Http\Message\ServerRequestInterface object for resolution
```

After the parsing is completed, a clone object of the matched route will be returned. If the corresponding variable stored in the object is not matched, a corresponding exception will be thrown.

## mpie/event

Events are implemented based on Psr-14 and can be used independently

### Install

```shell
composer require mpie/event
```

### use

1. You need to create a `Listener` class and implement the `listen` and `process` methods in `\Mpie\Event\Contracts\EventListenerInterface`. `listen`
   The method requires to return an array, the values ​​in the array are the events that the event listener listens to,
   The `process` method requires an event object to be passed in, this method does not need to return a value, for example

```php
class UserStatusListener implements EventListenerInterface
{
    /**
    * Return the event the listener is listening for
    * @return string[]
    */
    public function listen():array {
        return [
            \App\Events\UserRegistered::class,
        ];
    }

    /**
    * Process after trigger event
    * @param object $event
    */
    public function process(object $event): void
    {
        $event->user = false;
    }
}
```

2. You need to create an `Event` class, for example

```php
class UserRegistered
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
```

3. Instantiate a `ListenerProvider`, the constructor needs to pass in all the listener objects

```php
$listenerProvider = new ListenerProvider(...[new UserStatusListener]);
```

4. Instantiate the scheduler and pass the `ListenerProvider` instance to the constructor

```php
$dispatcher = new \Mpie\Event\EventDispatcher($listenerProvider);
```

5. Event Scheduling

```php
$user = User::find(1);

$event = $dispatcher->dispatch(new UserRegistered($user));
```

6. Terminable Events

> The event implements the `isPropagationStopped` method in the `StoppableEventInterface` interface, and returns true, the event after the event will not be triggered

```php
class UserRegistered implements StoppableEventInterface
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function isPropagationStopped() : bool
    {
        return true;
    }
}
```

## mpie/aop

A simple Aop implementation. Support MpiePHP, Swoole, WebMan and other frameworks

### Install

```shell
composer require mpie/aop
```

### use

> The following takes webman as an example

#### Modify start.php file

```php
\Mpie\Di\Scanner::init(new \Mpie\Aop\ScannerConfig([
    'cache' => false,
    'paths' => [
        BASE_PATH . '/app',
    ],
    'collectors' => [],
    'runtimeDir' => BASE_PATH . '/runtime',
]));
```

* cache is cached, if true, the proxy class will not be regenerated next time it starts
* paths annotation scan path
* collectors User-defined annotation collectors
* When runtimeDir is running, the generated proxy class and proxy class map will be cached here

#### Write an aspect class and implement the AspectInterface interface

```php
<?php
namespace App\aspects;

use Closure;
use Mpie\Aop\JoinPoint;
use Mpie\Aop\Contract\AspectInterface;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Round implements AspectInterface
{
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        echo 'before';
        $response = $next($joinPoint);
        echo 'after';
        return $response;
    }
}

```

Modify the method to add facet annotations

```php
<?php
namespace app\controller;

use App\aspects\Round;
use Mpie\Di\Annotations\Inject;
use support\Request;

class Index
{
    #[Inject]
    protected Request $request;

    #[Round]
    public function index()
    {
        echo '--controller--';
        return response('hello webman');
    }
}
```

>
Note that two annotations have been added above. The functions of attribute and method annotations are injection attributes and entry methods, respectively. You can print the attribute $request directly in the controller and find that it has been injected. There can be multiple aspect annotations, which will be executed in order. The specific implementation can refer to these two classes. Note that the Inject annotation here does not obtain the instance from the webman container, so if you use it, you need to redefine Inject to ensure a single instance.

#### start up```shell
php start.php start
```

Open the browser to open the corresponding page, the console output content is `before--controller--after`

### Custom collectors and annotations

#### Defining the collector

```php
class Collector implements \Mpie\Di\Contracts\CollectorInterface
{
public static function collectClass(string $class, object $attribute): void
    {
    }

    public static function collectMethod(string $class, string $method, object $attribute): void
    {
    }

    public static function collectProperty(string $class, string $property, object $attribute): void
    {
    }
}
```

You can customize the collection behavior, if you only need one of these methods, you can extend the AbstractCollector class.

#### Registering the collector

Collectors can be passed to the collectors parameter of ScannerConfig

### Example

```php
interfaceValidationAttribute {

}

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidateRuleMax {
public function __construct(public function $max) {

}
}

class ValidationCollector extends AbstractCollector
{
protected array $container = [];

    public static function collectProperty(string $class, string $property, object $attribute): void
    {
    if(self::isValid($attribute)) {
    self::$container[$class][$property][] = $attribute;
    }
    }
    
    public static function getByClass(string $class) {
    return self::$container[$class] ?? [];
    }
    
    public static function isValid(object $attribute): bool {
    return $attribute instanceof ValidationAttribute;
    }
}

class DoSomething {

protected $a = '1212';

public function do() {
$properties = ValidationCollector::getByClass(__CLASS__);
    $len = $properties['a']->max;
        if(mb_strlen($this->a > $len)) {
        throw new InvalidArgumentException('Length is invalid.');
        }
}
}
```

### Slice

#### Create facet class

```php
<?php
namespace App\Aspects;

use Closure;
use Mpie\Di\Aop\JoinPoint;
use Mpie\Di\Contracts\AspectInterface;
use Mpie\Di\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionException as ReflectionExceptionAlias;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    protected CacheInterface $cache;

    public function __construct(protected int $ttl = 0, protected ?string $key = null)
    {
        $this->cache = make(CacheInterface::class);
    }

    public function process(JoinPoint $joinPoint, Closure $next)
    {
        $key = $this->key ?? serialize([$joinPoint->getProxy()::class, $joinPoint->getMethod(), $joinPoint->getArguments()]);
        return $this->cache->remember($key, function () use ($next, $joinPoint) {
            return $next($joinPoint);
        }, $this->ttl);
    }
}
```

> Note that this is an annotation class, and its usage is the same as that of laravel middleware. Note that if the Cacheable aspect is used, the controller method must not return the ResponseInterface response, but directly return an array or string. This problem will be solved later

#### add comments

```php
class IndexController
{
#[Cacheable(ttl: 100)]
public function index() {
return ['test'];
}
}
```

As shown above, the content of the controller's index method response will be cached for 100 seconds. As long as it is scanned by the Scanner and the proxy class can use the aspect, and the object instantiated by the new keyword can also be accessed.

## mpie/session

Session component that can be used independently, supports File and Redis storage

### Install

```php
composer require mpie/session
```

### use

```php
// Initialize SessionManager
$sessionManager = \Mpie\Di\Context::getContainer()->make(\Mpie\Session\SessionManager::class);

// Create a new Session session
$session = $sessionManager->create();

// start the session
$session->start($id); // create id if null

// set up
$session->set($key, $value);

// Get, support dot syntax
$session->get($key);

// The request ends, save the session
$session->save();

// close the session
$session->close();
```

> Code reference for middleware

```php
<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use Mpie\Http\Message\Cookie;
use Mpie\Session\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionMiddleware implements MiddlewareInterface
{
    protected int $expires = 9 * 3600;
    protected string $name = 'MPIE_SESS_ID';
    protected bool $httponly = true;
    protected string $path = '/';
    protected string $domain = '';
    protected bool $secure = true;

    public function __construct(protected SessionManager $sessionManager)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $this->sessionManager->create();
        $session->start($request->getCookieParams()[strtoupper($this->name)] ?? null);
        $request = $request->withAttribute('Mpie\Session\Session', $session);
        $response = $handler->handle($request);
        $session->save();
        $session->close();
        $cookie = newCookie(
            $this->name, $session->getId(), time() + $this->expires, $this->path, $this->domain, $this->secure, $this->httponly
        );

        return $response->withAddedHeader('Set-Cookie', $cookie->__toString());
    }
}

```

## mpie/http-server

Multi-environment compatible Http Server

### Design Thinking

> All comply with psr specification

request -> kernel ->response

### use

```php

// instantiate the router
$router = new \Mpie\Routing\Router();

// register route
$router->get('/', 'IndexController@index');

// Instantiate the kernel, note that this needs to remain a singleton
$kernel = new \Mpie\Http\Server\Kernel($router->getRouteCollector(), \Mpie\Di\Context::getContainer());

// Get a PsrServerRequest
$request = \Mpie\Http\Message\ServerRequest::createFromGlobals();

// return PsrResponse
$response = $kernel->through($request);

// send response
(new \Mpie\Http\Server\ResponseEmitter\FPMResponseEmitter())->emit($response);

```
> The framework has built-in ResponseEmitter for three environments, all of which can be customized

### Example

> FPM environment

```php
(function() {
    $loader = require_once '../vendor/autoload.php';
    /** @var Kernel $kernel */
    $kernel = Context::getContainer()->make(Kernel::class);
    $response = $kernel->through(ServerRequest::createFromGlobals());
    (new FPMResponseEmitter())->emit($response);
})();
```

You can also override some of the methods by inheriting the Kernel class or put it into global middleware

## mpie/view

`MpiePHP` view component, supports `Blade`, extensible driver, can be used independently

### Install

```
composer require mpie/view
```

### use

The syntax supported by the Blade engine is as follows

- {{}}
- {{-- --}}
- {!!!!}
- @extends
- @yield
- @php
- @include
- @if
- @unless
- @empty
- @isset
- @foreach
- @for
- @switch
- @section

> If using `extends` + `yield` + `section`, make sure that all code in the subtemplate except `extends` is wrapped by `section`

#### config file

After the installation is complete, the framework will automatically move the configuration file `view.php` to the `config` directory of the root package. If the creation fails, you can create it manually. The contents of the file are as follows:

```php
<?php

return [
    'engine' => '\Mpie\View\Engine\BladeEngine',
    'options' => [
        // template directory
        'path' => __DIR__ . '/../views/',
        // compile and cache directories
        'compile_dir' => __DIR__ . '/../runtime/cache/views/compile',
        // template cache
        'cache' => false,
        // template suffix
        'suffix' => '.blade.php',
    ],
];

## use

```php
$viewFactory = new ViewFactory(config('view'));
$renderer = $viewFactory->getRenderer();
$renderer->assign('key', 'value');
$renderer->render('index', ['key2' => 'value2']);
```

#### custom engine

Custom engines must implement the `ViewEngineInterface` interface

## mpie/cache

The storage component is developed based on `Psr-16`, supports `file`, `memcached`, `redis`, `Apc`, the component can be used independently

### Install

```shell
composer require mpie/cache
```

### use

```php
public function index(\Psr\SimpleCache\CacheInterface $cache) {
    var_dump($cache->get('key'));
}
```

#### extensions

When the `$key` cache does not exist, the closure will be called and the return value of the closure will be written to the cache and returned, `$ttl` is the cache time` (s)`

```php
Cache::remember($key, function() {
    return 'cache';
}, ?int $ttl = null)
```

auto increment

```php
Cache::incr($key, int $step = 1)
```

Decrement

```php
Cache::decr($key, int $step = 1)
```

> Self-incrementing and self-decrementing step means the step size, if the cache does not exist, it will increase from 0

## mpie/database

> Documents are not updated in time, there may be a lot of discrepancies

### Install

> The documentation is not updated in time, and the usage methods are very different

```
composer require mpie/database
```

> Support mysql, pgsql

The current `DB` class only supports `mysql` well, and other databases have not been tested for the time being. If necessary, you can use `composer` to install third-party database operation classes, for example: `medoo`
, `thinkorm`

```php
class UserDao {
#[Inject]
    protected \Mpie\Database\Query $query;
    
    public function get() {
    return $this->query->table('users')->get();
    }
}
```

Need to use injection to inject the `Mpie\Database\Query` class

#### new

```
$query->table('users')->insert(['name' => 'username','age' => 28]);
```

#### delete

```
$query->table('users')->where('id', '10', '>')->delete();
```

Delete users with id greater than 10.

#### renew

```
$query->table('users')->where('id', '10', '>')->update(['name' => 'zhangsan']);
```

#### Inquire

> Query Builder

There are mainly the following methods: `table`, `where`, `whereLike`, `whereExists`, `whereBetween`
,`whereNull`,`whereNotNull`,`order`,`group`,`join`,`leftJoin`,`rightJoin`,`limit`.

#### table

```
table(string $table)
```

`$table` must be prefixed if there is a prefix

#### order

```
$query->table('users')->order(['id' => 'DESC','sex' => 'DESC'])->select();
```

The final SQL could be

```
SELECT * FROM users ORDER BY id DESC, sex DESC;
```

#### group

```php
$query->table('users')->group(['sex','id' => 'sex = 1'])->get();
```

The final SQL could be

```
SELECT * FROM users GROUP BY sex,id HAVING sex = 1;
```

#### limit

```php
$query->table('users')->limit(1,3)->get();
$query->table('users')->limit(1)->get();
```

Depending on the database the final SQL could be

```
SELECT * FROM users LIMIT 3,1;
SELECT * FROM users LIMIT 1;
```

may also be

```
SELECT * FROM users LIMIT 1 OFFSET 3;
```

#### join

There are three ways to join the table `innerJoin`leftJoin`rightJoin`

For example the following statement:

```
$query->table('users')->join('books')->on('books.user_id', '=', 'users.id')->get();

$query->table('users')->leftJoin('books')->on('books.user_id', '=', 'users.id')->get();

$query->table('users')->rightJoin('books')->on('books.user_id', '=', 'users.id')->get();
```

The final SQL could be

```
SELECT * FROM users INNER JOIN books on books.user_id = users.id;
SELECT * FROM users LEFT JOIN books on books.user_id = users.id;
SELECT * FROM users RIGHT JOIN books on books.user_id = users.id;
```

#### where

For example I have the following query

```
$query->table('users')->where(['id' => 1, 'sex = 0'])->select();
$query->table('users')->where(['id' => 2], '>=')->select();
```

The final SQL might be as follows

```
SELECT * FROM users WHERE id = ? AND sex = 0;SELECT * FROM users WHERE id >= ?;
```

It can be seen that `id = ?` and `sex = 0` indicate that the id condition can be preprocessed, but the key of the condition array is a number, but it will not be processed.

#### whereLike

For example I have the following query

```
$query->table('users')->whereLike(['username' => 1, 'sex = 0'])->select();
```

The final SQL could be as follows

```
SELECT * FROM users WHERE username LIKE ? AND sex = 0;
```

### use

#### Query one can use the get method

> Query a piece of data with an id of 1 in the users table, usually with a conditional statement to locate the data

```
$query->table('users')->where(['id' => 1])->get()
```

#### Query a value

Query the `username` whose `id` is `2` in the `users` table

```
$query->table('users')->where(['id' => 2])->value('username');
```

#### Total query

Query the total data of `users` table, return `int`

```
$query->table('users')->count();
```

> The `count()` method can pass in a parameter that is the column name, if not passed, the default is *

#### Query multiple available `select` methods

> Query the 2 entries in the users table whose id is in the range of 1, 2, and 3, and their offset is 3

```
$query->table('users')->field('id')->whereIn(['id' => [1,2,3]])->limit(2,3)->select();
```

The query is a dataset object, which can be obtained using `toArray` or `toJson`, for example

```
$query->table('users')->limit(1)->select()->toArray();
```

#### Query a column value

Query the `username` column in the `users` table

```
$query->table('users')->column('username');
```

### Transactions

```
$res = $query->transaction(function (Query $query, \PDO $pdo) {
//$pdo->setAttribute(\PDO::ATTR_ORACLE_NULLS,true); You can set the required parameters by yourself
$deletedUsers = $query->name('users')->whereIn(['id' => [1,2,3]])->delete();
    if(0 == $deletedUsers){
        throw new \PDOException('No user was deleted!');
    }
    return $query->name('books')->whereIn(['user_id' => [1,2,3]])->delete();
});
```

Where `transaction` accepts a closure parameter, the callback can pass in two parameters, one is the current query instance, the other is `PDO`
Example, you can see that two `SQL` are executed here
, you can manually throw `PDOException` when the execution result is not satisfied
Exception to roll back the transaction, otherwise if the execution process throws an exception, it will automatically roll back the transaction. Commit the transaction after the execution ends without errors. The closure needs to return the execution result, and the returned execution result will be `transaction`
method returns.

#### Model

> The directory of the model is under `app\Models`, and the new model inherits `Mpie\Database\Eloquent\Model`
> The class can use the methods provided by the model. The model name is the table name. You can also set the $table attribute in the model. At this time, the table name is the value of name.

For example, I created a new Notes model, which can be used directly in the model

```php
class Notes extends \Mpie\Database\Eloquent\Model
{
    protected string $table = 'notes';
    
    protected array $fillable = [
        'title', 'text', 'publication_date'
    ];
    
    protected array $cast = [
        'publication_date' => 'integer'
    ];
}
```

The following methods can be used

```
User::query()->where()->get() // The operation after using the query method is the same as the query builder
User::first(); // returns the first model
User::find($id); // returns a single User model
User::get(); // Returns a collection of all User models
```

## mpie/http-message

Documentation to be completed

## mpie/queue

Documentation to be completed

## mpie/redis

Documentation to be completed

## mpie/utils

Documentation to be completed

## mpie/validator

Documentation to be completed
