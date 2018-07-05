<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;

final class InMemoryStorage implements Reader
{
    private $data;
    private $ttl;

    public function __construct(array $data, int $ttl = 3600)
    {
        $this->data = $data;
        $this->ttl = $ttl;
    }

    public function has(string $key): bool
    {
        return (bool) array_search($key, $this->data);
    }

    public function get(string $key): Key
    {
        if (false === $identity = array_search($key, $this->data)) {
            throw new KeyNotFound();
        }

        return new Key($key, $identity, $this->ttl);
    }
}
