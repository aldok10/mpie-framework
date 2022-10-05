<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\VarDumper\Adapter;

use ErrorException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Mpie\VarDumper\Abort;
use Mpie\VarDumper\AbortHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HyperfAbortHandler extends ExceptionHandler
{
    use AbortHandler;

    /**
     * @param Abort $e
     *
     * @throws ErrorException
     */
    public function handle(Throwable $e, ResponseInterface $response)
    {
        $this->stopPropagation();

        return $response->withBody(new SwooleStream($this->convertToHtml($e)));
    }

    public function isValid(Throwable $e): bool
    {
        return $e instanceof Abort;
    }
}
