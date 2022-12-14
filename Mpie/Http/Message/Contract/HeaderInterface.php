<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Message\Contract;

interface HeaderInterface
{
    public const HEADER_CONTENT_TYPE = 'Content-Type';

    public const HEADER_SET_COOKIE = 'Set-Cookie';

    public const HEADER_PRAGMA = 'Pragma';

    public const HEADER_ACCEPT = 'Accept';

    public const HEADER_EXPIRES = 'Expires';

    public const HEADER_CACHE_CONTROL = 'Cache-Control';

    public const HEADER_X_FORWARDED_FOR = 'X-Forwarded-For';

    public const HEADER_X_CSRF_TOKEN    = 'X-Csrf-Token';

    public const HEADER_X_XSRF_TOKEN    = 'X-Xsrf-Token';

    public const HEADER_CONTENT_TRANSFER_ENCODING = 'Content-Transfer-Encoding';

    public const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';

    public const HEADER_AUTHORIZATION = 'Authorization';

    public const HEADER_X_REQUESTED_WITH = 'X-Requested-With';

    public const HEADER_ORIGIN = 'Origin';

    public const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';

    public const HEADER_ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';

    public const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';

    public const HEADER_ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';

    public const HEADER_ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
}
