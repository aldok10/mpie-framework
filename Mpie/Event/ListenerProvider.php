<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Event;

use Mpie\Event\Contract\EventListenerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<class-string, EventListenerInterface[]>
     */
    protected array $events = [];

    /**
     * Registered listener.
     *
     * @var array<class-string, EventListenerInterface[]>
     */
    protected array $listeners = [];

    /**
     * Register a single event listener.
     */
    public function addListener(EventListenerInterface $eventListener)
    {
        $listener = $eventListener::class;
        if (! $this->listened($listener)) {
            $this->listeners[$listener] = $eventListener;
            foreach ($eventListener->listen() as $event) {
                $this->events[$event][] = $eventListener;
            }
        }
    }

    /**
     * Determine if it has been monitored.
     */
    public function listened(string $listeners): bool
    {
        return isset($this->listeners[$listeners]);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->events[$event::class] ?? [];
    }
}
