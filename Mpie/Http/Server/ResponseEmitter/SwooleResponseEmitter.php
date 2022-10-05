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
use Swoole\Http\Response;

class SwooleResponseEmitter implements ResponseEmitterInterface
{
    /**
     * @param Response $sender
     */
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        $sender->status($psrResponse->getStatusCode(), $psrResponse->getReasonPhrase());
        foreach ($psrResponse->getHeader(HeaderInterface::HEADER_SET_COOKIE) as $cookieLine) {
            $cookie = Cookie::parse($cookieLine);
            $sender->cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpires(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttponly(),
                $cookie->getSameSite()
            );
        }
        $psrResponse = $psrResponse->withoutHeader(HeaderInterface::HEADER_SET_COOKIE);
        foreach ($psrResponse->getHeaders() as $key => $value) {
            $sender->header($key, implode(', ', $value));
        }
        $body = $psrResponse->getBody();
        switch (true) {
            case $body instanceof FileStream:
                $sender->sendfile($body->getFilename(), $body->getOffset(), $body->getLength());
                break;
            default:
                $sender->end($body?->getContents());
        }
        $body?->close();
    }
}
