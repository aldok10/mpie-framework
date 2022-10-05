<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Routing\Annotation;

use Attribute;
use Mpie\Http\Message\Contract\RequestMethodInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class RequestMapping
{
    /**
     * @var array<int, string>
     */
    public array $methods = [
        RequestMethodInterface::METHOD_GET,
        RequestMethodInterface::METHOD_HEAD,
        RequestMethodInterface::METHOD_POST,
    ];

    /**
     * @param string             $path        path
     * @param array<int, string> $methods     method
     * @param array<int, string> $middlewares middleware
     */
    public function __construct(
        public string $path = '/',
        array $methods = [],
        public array $middlewares = [],
    ) {
        if (! empty($methods)) {
            $this->methods = $methods;
        }
    }
}
