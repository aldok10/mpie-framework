<?php

declare(strict_types=1);

/**
 * This file is part of Mpie Framework.
 *
 * @link     https://github.com/aldok10/mpie-framework
 * @license  https://github.com/aldok10/mpie-framework/blob/master/LICENSE
 */

namespace Mpie\Http\Message;

use Mpie\Http\Message\Bag\HeaderBag;
use Mpie\Http\Message\Stream\StandardStream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    protected string $protocolVersion = '1.1';

    protected HeaderBag $headers;

    protected ?StreamInterface $body = null;

    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion($version): MessageInterface
    {
        if ($this->protocolVersion === $version) {
            return $this;
        }
        $new = clone $this;
        return $new->setProtocolVersion($version);
    }

    public function setProtocolVersion($version): static
    {
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->headers->all();
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader($name): ?bool
    {
        return $this->headers->has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine($name): string
    {
        if ($this->hasHeader($name)) {
            return implode(', ', $this->getHeader($name));
        }
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader($name, $value): MessageInterface
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        return $new->setHeader($name, $value);
    }

    public function setHeader($name, $value): MessageInterface
    {
        $this->headers->set($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        return $new->setAddedHeader($name, $value);
    }

    public function setAddedHeader($name, $value): MessageInterface
    {
        $this->headers->add($name, $value);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader($name): MessageInterface
    {
        $new          = clone $this;
        $new->headers = clone $this->headers;
        $new->headers->remove($name);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): ?StreamInterface
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        return $new->setBody($body);
    }

    public function setBody(StreamInterface $body): MessageInterface
    {
        $this->body = $body;
        return $this;
    }

    protected function formatBody(string|StreamInterface|null $body)
    {
        $this->body = $body ? StandardStream::create($body) : null;
    }
}
