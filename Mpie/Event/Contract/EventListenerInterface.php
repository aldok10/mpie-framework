<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Event\Contract;

interface EventListenerInterface
{
    /**
     * @return iterable<mixed, class-string>
     */
    public function listen(): iterable;

    public function process(object $event): void;
}
