<h1 align="center">
Mpie Framework Starter
</h1>

<p align="center">Lightweight • Simple • Fast</p>

A componentized lightweight `PHP` framework that supports swoole, workerman, and FPM environments. It can be used for `API` development, which is convenient and fast. The framework has the `session` and `view` extensions installed by default, and can be removed directly if not needed.

## Environmental requirements

```
- PHP >= 8.0
- ext-ctype
- ext-dom
- ext-filter
- ext-json
- ext-libxml
- ext-mbstring
- ext-openssl
- ext-openswoole
- ext-pcntl
- ext-pdo
- ext-phar
- ext-redis
- ext-tokenizer
- ext-xml
- ext-xmlwriter
```

> If you use openswoole, be sure to install version 4.8 or above, if you use workerman, be sure to use version 4.0 or above

## use

### Install

```shell
composer create-project mpie22/framework:dev-main
```

### Start the service

> swoole service

```php
php bin/openswoole.php // asynchronous mode
php bin/openswoole-co.php // coroutine mode
```

> workerman service

```php
php bin/workerman.php start
```

> Built-in services

```php
php bin/cli-server.php
```

> FPM mode, direct the request to public/index.php

## the difference

Using swoole/workerman to support annotations, AOP and other features, FPM mode can directly uninstall AOP packages.

## Getting started

### route definition

> Annotation definitions can be used under swoole/swooleco/workerman

```php
<?php

namespace App\Controllers;

use App\Http\Response;
use Mpie\Routing\Annotation\Controller;
use Mpie\Routing\Annotation\GetMapping;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

#[Controller(prefix: '/')]
class IndexController
{
    #[GetMapping(path: '/')]
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        return Response::HTML('Hello, ' . $request->query('name', 'MpiePHP!'));
    }
}

```

The above request `0.0.0.0:8080` will point to the `index` method, the controller method supports dependency injection, if you need an example of the current request, the request parameter name must be `request`, other routing parameters will be injected, the controller method An instance of `ResponseInterface` needs to be returned.

> Annotations cannot be used under FPM or built-in services

Route definitions are defined in the `map` method of the `App\Http\Kernel` class

```php
$router->middleware(TestMiddleware::class)->group(function(Router $router) {
    $router->get('/', [IndexController::class, 'index']);
    $router->get('/test', function(\Psr\Http\Message\ServerRequestInterface $request) {
        return \App\Http\Response::HTML('new');
    });
});
```

## More

* [Components](/docs/en/components.md "component list")
* [Framework Development](/docs/en/framework.md "Framework Development")

## Thanks