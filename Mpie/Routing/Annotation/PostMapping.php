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
class PostMapping extends RequestMapping
{
    /**
     * @var array<int, string>
     */
    public array $methods = [RequestMethodInterface::METHOD_POST];
}
