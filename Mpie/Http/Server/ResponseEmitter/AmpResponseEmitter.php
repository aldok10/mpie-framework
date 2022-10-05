<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server\ResponseEmitter;

use Amp\Http\Server\Response;
use Mpie\Http\Server\Contract\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;

class AmpResponseEmitter implements ResponseEmitterInterface
{
    public function emit(ResponseInterface $psrResponse, $sender = null)
    {
        return new Response($psrResponse->getStatusCode(), $psrResponse->getHeaders(), $psrResponse->getBody());
    }
}
