<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Cache\Exception;

use Exception;
use Psr\SimpleCache\CacheException as PsrCacheException;

class CacheException extends Exception implements PsrCacheException
{
}
