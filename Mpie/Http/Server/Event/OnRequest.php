<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Server\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OnRequest
{
    public float $requestedAt;

    public function __construct(
        public ServerRequestInterface $request,
        public ?ResponseInterface $response = null
    ) {
        $this->requestedAt = microtime(true);
    }
}
