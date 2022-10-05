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
use Mpie\Http\Message\Contract\RequestMethodInterface;
use Mpie\Http\Message\Contract\StatusCodeInterface;
use Mpie\Http\Message\Response;
use Mpie\Utils\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Mpie\Utils\collect;

class AllowCrossDomain implements MiddlewareInterface
{
    /**
     * @var array Allow domains, all can use `*`
     */
    protected array $allowOrigin = ['*'];

    /**
     * @var string[] Allowed headers
     */
    protected array $allowHeaders = [
        HeaderInterface::HEADER_AUTHORIZATION,
        HeaderInterface::HEADER_CONTENT_TYPE,
        'If-Match',
        'If-Modified-Since',
        'If-None-Match',
        'If-Unmodified-Since',
        'X-Csrf-Token',
        HeaderInterface::HEADER_X_REQUESTED_WITH,
    ];

    /**
     * @var array|string[] Allowed methods
     */
    protected array $allowMethods = [
        RequestMethodInterface::METHOD_GET,
        RequestMethodInterface::METHOD_POST,
        RequestMethodInterface::METHOD_PATCH,
        RequestMethodInterface::METHOD_PUT,
        RequestMethodInterface::METHOD_DELETE,
        RequestMethodInterface::METHOD_OPTIONS,
    ];

    /**
     * @var string Allow access to credentials
     */
    protected string $allowCredentials = 'true';

    /**
     * @var int Cookie lifetime
     */
    protected int $mpieAge = 1800;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->shouldCrossOrigin($origin = $request->getHeaderLine(HeaderInterface::HEADER_ORIGIN))) {
            $headers = $this->createCORSHeaders($origin);
            if (strcasecmp($request->getMethod(), RequestMethodInterface::METHOD_OPTIONS) === 0) {
                return new Response(StatusCodeInterface::STATUS_NO_CONTENT, $headers);
            }

            return $this->addHeadersToResponse($handler->handle($request), $headers);
        }

        return $handler->handle($request);
    }

    /**
     * Create response headers.
     */
    protected function createCORSHeaders(string $origin): array
    {
        return [
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS => $this->allowCredentials,
            HeaderInterface::HEADER_ACCESS_CONTROL_MAX_AGE           => $this->mpieAge,
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_METHODS     => implode(', ', $this->allowMethods),
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_HEADERS     => implode(', ', $this->allowHeaders),
            HeaderInterface::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN      => $origin,
        ];
    }

    /**
     * Add headers to the response.
     */
    protected function addHeadersToResponse(ResponseInterface $response, array $headers): ResponseInterface
    {
        foreach ($headers as $name => $header) {
            $response = $response->withHeader($name, $header);
        }
        return $response;
    }

    /**
     * Allow cross domain.
     */
    protected function shouldCrossOrigin(string $origin)
    {
        if (empty($origin)) {
            return false;
        }
        return collect($this->allowOrigin)->first(function ($allowOrigin) use ($origin) {
            return Str::is($allowOrigin, $origin);
        });
    }
}
