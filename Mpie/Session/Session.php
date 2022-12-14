<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Session;

use InvalidArgumentException;
use Mpie\Session\Exception\SessionException;
use Mpie\Utils\Arr;
use SessionHandlerInterface;

use function ctype_alnum;
use function session_create_id;
use function unserialize;

class Session
{
    protected string $id        = '';

    protected array  $data      = [];

    protected bool   $started   = false;

    protected bool   $destroyed = false;

    public function __construct(
        protected SessionHandlerInterface $sessionHandler
    ) {
    }

    /**
     * Start a new session.
     */
    public function start(string $id = ''): void
    {
        if ($this->isStarted()) {
            throw new SessionException('Cannot restart session.');
        }
        $this->id = ($id && $this->isValidId($id)) ? $id : session_create_id();
        if ($data = $this->sessionHandler->read($this->id)) {
            $this->data = (array) (unserialize($data) ?: []);
        }
        $this->started = true;
    }

    /**
     * Save session data.
     */
    public function save(): void
    {
        $this->sessionHandler->write($this->id, serialize($this->data));
    }

    /**
     * Generate a new session id.
     */
    public function regenerateId(): void
    {
        $this->id = session_create_id();
    }

    /**
     * Close the session.
     */
    public function close(): bool
    {
        return $this->sessionHandler->close();
    }

    /**
     * Check whether the key exists.
     */
    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Get the value of $key, or $default if it does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * Set value of the key.
     */
    public function set(string $key, mixed $value): void
    {
        Arr::set($this->data, $key, $value);
    }

    /**
     * Gets and deletes, or returns default if it does not exist.
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $data = $this->get($key, $default);
        $this->remove($key);
        return $data;
    }

    /**
     * Delete data for $key.
     */
    public function remove(string $key): void
    {
        Arr::forget($this->data, $key);
    }

    /**
     * Return all session data.
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Destroy the session.
     */
    public function destroy(): void
    {
        $this->sessionHandler->destroy($this->id);
        $this->data      = [];
        $this->id        = '';
        $this->destroyed = true;
    }

    /**
     * Get the session id.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set the session id.
     */
    public function setId(string $id): void
    {
        if (! $this->isValidId($id)) {
            throw new InvalidArgumentException('The length of the session ID must be 40.');
        }
        $this->id = $id;
    }

    /**
     * Check whether the session is started.
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Check whether the session is destroyed.
     */
    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }

    /**
     * Check whether $id is a valid session id.
     */
    protected function isValidId(string $id): bool
    {
        return ctype_alnum($id);
    }
}
