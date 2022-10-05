<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server\Middleware;

use Exception;
use Mpie\Http\Message\Contract\HeaderInterface;
use Mpie\Http\Message\Contract\RequestMethodInterface;
use Mpie\Http\Message\Cookie;
use Mpie\Http\Server\Exception\CSRFException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Mpie\Utils\collect;

class VerifyCSRFToken implements MiddlewareInterface
{
    /**
     * Exclude, do not verify CSRF Token.
     */
    protected array $except = ['/'];

    /**
     * Expiration.
     */
    protected int $expires = 9 * 3600;

    /**
     * The request method that needs to be authenticated.
     */
    protected array $shouldVerifyMethods = [
        RequestMethodInterface::METHOD_POST,
        RequestMethodInterface::METHOD_PUT,
        RequestMethodInterface::METHOD_PATCH,
    ];

    /**
     * @throws CSRFException|Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->shouldVerify($request)) {
            if (is_null($previousToken = $request->getCookieParams()[HeaderInterface::HEADER_X_CSRF_TOKEN] ?? null)) {
                $this->abort();
            }

            $token = $this->parseToken($request);

            if ($token === '' || $token !== $previousToken) {
                $this->abort();
            }
        }

        return $this->addCookieToResponse($handler->handle($request));
    }

    /**
     * Get CSRF/XSRF Token from the header, if none exist, get the parameter of form submission as the value of __token.
     */
    protected function parseToken(ServerRequestInterface $request): string
    {
        return $request->getHeaderLine(HeaderInterface::HEADER_X_CSRF_TOKEN)
            ?: $request->getHeaderLine(HeaderInterface::HEADER_X_XSRF_TOKEN)
                ?: ($request->getParsedBody()['__token'] ?? '');
    }

    /**
     * Add the token to the cookie.
     *
     * @throws Exception
     */
    protected function addCookieToResponse(ResponseInterface $response): ResponseInterface
    {
        $cookie = new Cookie(HeaderInterface::HEADER_X_CSRF_TOKEN, $this->newCSRFToken(), time() + $this->expires);
        return $response->withAddedHeader(HeaderInterface::HEADER_SET_COOKIE, $cookie->__toString());
    }

    /**
     * Generate CSRF Token.
     *
     * @throws Exception
     */
    protected function newCSRFToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * @throws CSRFException
     */
    protected function abort()
    {
        throw new CSRFException('CSRF token is invalid', 419);
    }

    /**
     * Does it need to be verified.
     */
    protected function shouldVerify(ServerRequestInterface $request): bool
    {
        if (in_array($request->getMethod(), $this->shouldVerifyMethods)) {
            return ! collect($this->except)->first(function ($pattern) use ($request) {
                return $request->is($pattern);
            });
        }
        return false;
    }
}
