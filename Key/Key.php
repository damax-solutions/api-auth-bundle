<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key;

final class Key
{
    private $key;
    private $identity;
    private $ttl;

    public function __construct(string $key, string $identity, int $ttl)
    {
        $this->key = $key;
        $this->identity = $identity;
        $this->ttl = $ttl > 0 ? $ttl : 0;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function identity(): string
    {
        return $this->identity;
    }

    public function ttl(): int
    {
        return $this->ttl;
    }

    public function expired(): bool
    {
        return !$this->ttl();
    }

    public function __toString(): string
    {
        return $this->key;
    }
}
