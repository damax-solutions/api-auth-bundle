<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key;

final class Key
{
    private $key;
    private $username;
    private $ttl;

    public function __construct(string $key, string $username, int $ttl)
    {
        $this->key = $key;
        $this->username = $username;
        $this->ttl = $ttl > 0 ? $ttl : 0;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function username(): string
    {
        return $this->username;
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
