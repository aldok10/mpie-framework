<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server\ResponseEmitter;

use Mpie\Http\Message\Contract\HeaderInterface;
use Mpie\Http\Message\Cookie;
use Mpie\Http\Message\Stream\FileStream;
use Mpie\Http\Server\Contract\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;

class WorkerManResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param TcpConnection $sender
     */
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        $response    = new Response($psrResponse->getStatusCode());
        $cookies     = $psrResponse->getHeader(HeaderInterface::HEADER_SET_COOKIE);
        $psrResponse = $psrResponse->withoutHeader(HeaderInterface::HEADER_SET_COOKIE);
        foreach ($psrResponse->getHeaders() as $name => $values) {
            $response->header($name, implode(', ', $values));
        }
        $body = $psrResponse->getBody();
        if ($body instanceof FileStream) {
            $sender->send($response->withFile($body->getFilename(), $body->getOffset(), $body->getLength()));
        } else {
            /** @var string[] $cookies */
            foreach ($cookies as $cookie) {
                $cookie = Cookie::parse($cookie);
                $response->cookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $cookie->getMaxAge(),
                    $cookie->getPath(),
                    $cookie->getDomain(),
                    $cookie->isSecure(),
                    $cookie->isHttponly(),
                    $cookie->getSameSite()
                );
            }
            $sender->send($response->withBody((string) $body?->getContents()));
        }
        $body?->close();
        $sender->close();
    }
}
