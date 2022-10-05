# nginx configuration reference

swoole/workerman

```
server {
    server_name www.domain.com;
    listen 80;

    location / {
        proxy_http_version 1.1;
        proxy_set_header Connection "keep-alive";
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        if (!-f $request_filename) {
             proxy_pass http://127.0.0.1:9501;
        }
    }
}
```

FPM

```
server {
    server_name www.domain.com;
    listen 80;
    root /data/project/public;
    index index.html index.php;

    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
        }
    }

    location ~ ^(.+\.php)(.*)$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

# route

## configure

The framework provides two functions, env and config, which can easily obtain the configuration. If you use the AOP package, you can also directly use the annotation to inject the configuration into the example

```php
class User
{
    #[\Mpie\Config\Annotations\Config(key: 'qcloud.user.secret_key', default = '123')]
    protected string $secretKey;
}
```

As above, secretKey will be injected automatically. If it does not exist in the configuration file, the default value is 123.

## route definition

Register the route in the `map` method of `app/Http/Kernel.php`. For the registration method, please refer to the mpie/routing component documentation, or use the annotation method

```php
#[Controller(prefix: 'index', middleware: [BasicAuthentication::class])]
class Index
{
    #[GetMapping(path: '/user/{id}\.html')]
    public function index(\Psr\Http\Message\ServerRequestInterface $request, $id)
    {
        return new \Mpie\Http\Message\Response(200, [], 'Hello, world!');
    }
}
```

The above code defines an Index controller, and uses the Controller annotation to set the route prefix to index, the middleware of all methods in this controller is `BasicAuthentication::class`, and uses `GetMapping`
The annotation defines a route, `path` is `/user/{id}.html`, then the actual request address can be `/index/user/1.html`, the supported annotations are as follows, corresponding to different requests method, where the request method corresponding to RequestMapping defaults to `GET`, `POST`, `HEAD`, which can be customized using the `method` parameter

- GetMapping
- PostMapping
- PutMapping
- DeleteMapping
- PatchMapping
- RequestMapping

# Controller

```php
class HomeController {

	#[GetMapping(path: '/{id}')]
	public functin index(ServerRequestInterface $request, $id) {
		// Do something.
	}
}
```

The controller is a singleton object, and the method corresponding to the route supports dependency injection, and the parameter named `request` will be injected into the current request class, which is not a singleton and is independent for each request. Route parameters will be injected according to the parameter name, and other parameters with type hints will also be injected

# ask

The request can be any instance of ServerRequestInterface that implements Psr

> Please use `App\Http\ServerRequest`, which inherits `Mpie\Http\Message\ServerRequest` class and implements `Psr7 ServerRequest`
> The request class, and some simple methods are attached, developers can customize related methods

## request headers

```php
\App\Http\ServerRequest::getHeaderLine($name): string
\App\Http\ServerRequest::head($name): string
```

The above two methods will return the request header string, the return value of the `header` method `getHeaderLine` is the same

## Server

```php
\App\Http\ServerRequest::server($name): string
\App\Http\ServerRequest::getServerParams(): array
```

Get the value in `$_SERVER`

## Determine the request method

```php
\App\Http\ServerRequest::isMethod($method): bool
```

Case-insensitive way to determine whether the request method is consistent

## request address

```php
\App\Http\ServerRequest::url(bool $full = false): string
```

Returns the requested address, if `$full` is `true`, it returns the full address

## Cookie

```php
\App\Http\ServerRequest::cookie(string $name): string
\App\Http\ServerRequest::getCookieParams(): array
```

Get the requested cookie, generally you can also get it directly from `Header`

## Ajax

```php
\App\Http\ServerRequest::isAjax(): bool
```

Determine whether the current request is an `Ajax` request. Note: Some front-end frameworks do not send the X_REQUESTED_WITH header when sending an Ajax request, so this method will return false

## Determine path

```php
\App\Http\ServerRequest::is(string $path): bool
```

Determines whether the `path` of the current request matches the given `path`, supports regular expressions

## Get input parameters

```php
\App\Http\ServerRequest::get($key = null, $default = null)                         // $_GET
\App\Http\ServerRequest::post($key = null, $default = null)                        // $_POST
\App\Http\ServerRequest::all()                                                     // $_GET + $_POST
\App\Http\ServerRequest::input($key = null, $default = null, ?array $from = null)  // $_GET + $_POST
```

Get the parameters of the request. These parameters are loaded through PHP global variables. When $key is null, all parameters will be returned. If it is a string, it will return a single parameter. If it does not exist, return default. If $key is an array, it will be returned. Returns multiple parameters, $default can be an array at this time, the key of the array is the parameter key, and the value of the array is the default value of the parameter

E.g

```php
\App\Http\ServerRequest::get('a');
```

You can pass a default value to the second parameter, for example

```php
\App\Http\ServerRequest::get('a','default');
```

To get multiple parameters you can use

```php
\App\Http\ServerRequest::get(['a','b']);
```

You can pass in an associative array, the key of the array is the parameter name and the value is the default value, for example

```php
\App\Http\ServerRequest::get(['a', 'b'], ['a' => 1]);
```

At this point, if `a` does not exist, the value of `a` is `1`

## Document

```php
\App\Http\ServerRequest::getUploadedFiles();
```

It can be used under cli-server and FPM, but it has not been parsed under swoole or workerman.

## Middleware

> The middleware is implemented based on `Psr15`, and the global middleware registered in the `$middlewares` array in `App\Http\Kernel`, such as request exception handling, routing service, session initialization, CSRF verification, etc.

First you need to create a middleware, for example

```php
<?php
    
namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Login implement MiddlewareInterface
{ 
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface  
    {        
        // Pre-operation
        $response = $handler->handle($request);
        // Post operation     
        return $response;    
}
```

### Annotation

```php
#[Controller(prefix: '/', middleware(TestMiddleware::class))]
class Index {
	#[GetMapping(path: '/', middlewares: [Test2Middleware::class])]
	public function index() {
		// Do something.
	}
}
```

The above annotation defines two middleware. The methods in the controller Index are registered with the `TestMiddleware` middleware, and the `index` method not only includes the `TestMiddleware`, but also the `Test2Middleware` middleware.

# Session

> Session can use `File`, `Redis` driver

The Session configuration file is as follows

```php
<?php

return [
    'name'          => 'MPIE_SESS_ID',
    'handler'       => [
        'class'   => '\Mpie\Session\Handlers\File',
        'options' => [
            'path' => env('storage_path') . 'session',
            'ttl' => 3600,
        ]
    ],
    'cookie_expire' => time() + 3600,
];

```

## use in controller

The currently requested session needs to be created in the middleware, so SessionMiddleware needs to be enabled. After opening, put the session into the Request attribute and use it in the controller

```php
public function index(App\Http\ServerRequest $request)
{
     $session = $request->session();
     $session = $request->getAttribute(\Mpie\Session\Session::class);
}
```

You can also define the storage location of the session yourself, but you must ensure isolation between coroutines. If you use workerman, you can also use the session provided by it directly

## Check if it exists

```php
$session->has($name): bool
```
## Obtain

```php
$session->get($name)
```

## Add to

```php
$session->set($name, $value): bool
```

Can be an array or a string

## get and delete

```php
$session->pull($name): bool
```

## delete

```php
$session->remove($name): bool
```

## destroy
```php
$session->destory(): bool
```

# validator

To use the validator, the validator component needs to be installed

```shell
composer require mpie/validator
```

## Use

```php
$validator = new \Mpie\Validator\Validator();
$validator->make([
    'name' => 'mpiephp',
], [
    'name' => 'required|max:10',
], [
    'name.required' => 'name is required',
    'name.max'      => 'The maximum length of the name is 10',
])

// Validation failed
if($validator->fails()){
    // print all errors
    dd($validator->failed());
}
// Get a list of validated fields
$data = $validator->valid();
```

The above validation will validate all, if the validation fails you can get the first error

```php
$validator->errors()->first();
```

If you need to throw an exception on validation failure

```php
$validator->setThrowable(true);
```
# error handling

The framework inherits filp/whoops, which makes it easy to view exceptions. Before use, you need to add the exception handling class `Mpie\Framework\Exceptions\Handlers\WhoopsExceptionHandler` to the `App/Http/Middlewares/ExceptionHandleMiddleware` middleware

If it is not installed, you need to execute the following command to install

```shell
composer require filp/whoops
```

# print variables

The symfony/var-dumper component is used to print variables, but for compatibility with multiple environments, it is recommended to use the `d` function instead of the `dump`, `dd` functions. Before use, you need to add the exception handling class `Mpie\Framework\Exceptions\Handlers\VarDumperAbortHandler` to the `App/Http/Middlewares/ExceptionHandleMiddleware` middleware

```php
d(mixed ... $vars)
```

If you don't have `symfony/var-dumper` installed, you need to install it first

```shell
composer require symfony/var-dumper
```

You can pass in multiple variables, if you use swoole/workerman, you need to restart the service

> Special Note: Exception handling uses middleware. Exceptions not handled by middleware need to be handled manually by the user, so the code executed outside the middleware cannot use the d function to print variables

# swagger documentation

The following extensions are recommended

https://zircote.github.io/swagger-php