<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;

final class FixedStorage implements Reader
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

    /**
     * @throws KeyNotFoundException
     */
    public function get(string $key): Key
    {
        if (false === $username = array_search($key, $this->data)) {
            throw new KeyNotFoundException();
        }

        return new Key($key, $username, time() + $this->ttl);
    }
}
