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
use Mpie\Http\Server\Contract\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;

class FPMResponseEmitter implements ResponseEmitterInterface
{
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        if (! headers_sent()) {
            static::sendHeaders($psrResponse);
        }
        static::sendContent($psrResponse);

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
        }
    }

    protected static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level  = count($status);
        $flags  = defined('PHP_OUTPUT_HANDLER_REMOVABLE') ? PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE) : -1;

        while ($level-- > $targetLevel && ($s = $status[$level]) && (! isset($s['del']) ? ! isset($s['flags']) || $flags === ($s['flags'] & $flags) : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    protected static function sendHeaders(ResponseInterface $response)
    {
        header(sprintf('HTTP/%s %d %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()), true);
        foreach ($response->getHeader(HeaderInterface::HEADER_SET_COOKIE) as $cookie) {
            header(sprintf('%s: %s', HeaderInterface::HEADER_SET_COOKIE, $cookie), false);
        }
        $response = $response->withoutHeader(HeaderInterface::HEADER_SET_COOKIE);
        foreach ($response->getHeaders() as $name => $value) {
            header($name . ': ' . implode(', ', $value));
        }
    }

    protected static function sendContent(ResponseInterface $response)
    {
        $body = $response->getBody();
        echo $body;
        $body?->close();
    }
}
