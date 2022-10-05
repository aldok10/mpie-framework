<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server\Middleware;

use Mpie\Http\Message\Contract\HeaderInterface;
use Mpie\Http\Message\Contract\StatusCodeInterface;
use Mpie\Http\Message\Exception\HttpException;
use Mpie\Http\Message\Response;
use Mpie\Http\Server\Contract\Renderable;
use Mpie\Utils\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ExceptionHandleMiddleware implements MiddlewareInterface
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected array $dontReport = [];

    /**
     * @throws Throwable
     */
    final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            if (! $this->shouldntReport($e)) {
                $this->report($e, $request);
            }
            return $this->render($e, $request);
        }
    }

    /**
     * Report exception.
     */
    protected function report(Throwable $e, ServerRequestInterface $request): void
    {
    }

    /**
     * Convert exception to ResponseInterface object.
     */
    protected function render(Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        if ($e instanceof Renderable) {
            return $e->render($request);
        }
        $message    = $e->getMessage();
        $statusCode = $this->getStatusCode($e);
        if (str_contains($request->getHeaderLine(HeaderInterface::HEADER_ACCEPT), 'application/json')
            || strcasecmp('XMLHttpRequest', $request->getHeaderLine('X-REQUESTED-WITH')) === 0) {
            return new Response($statusCode, [], json_encode([
                'status'  => false,
                'code'    => $statusCode,
                'data'    => $e->getTrace(),
                'message' => $message,
            ], JSON_UNESCAPED_UNICODE));
        }
        return new Response($statusCode, [], sprintf(
            '<html lang="zh"><head><title>%s</title></head><body><pre style="font-size: 1.5em; white-space: break-spaces"><p><b>%s</b></p><b>Stack Trace</b><br>%s</pre></body></html>',
            $message,
            $message,
            $e->getTraceAsString(),
        ));
    }

    protected function getStatusCode(Throwable $e)
    {
        return $e instanceof HttpException ? $e->getCode() : StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR;
    }

    /**
     * Ignore reported exceptions.
     */
    protected function shouldntReport(Throwable $e): bool
    {
        return ! is_null(Arr::first($this->dontReport, fn ($type) => $e instanceof $type));
    }

    /**
     * Whether the operating environment is cli.
     */
    protected function runningInConsole(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
