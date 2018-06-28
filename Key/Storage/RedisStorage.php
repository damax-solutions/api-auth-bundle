<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Predis\Client;

final class RedisStorage implements Storage
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function has(string $key): bool
    {
        return (bool) $this->client->exists($key);
    }

    /**
     * @throws KeyNotFoundException
     */
    public function get(string $key): Key
    {
        if (null === $username = $this->client->get($key)) {
            throw new KeyNotFoundException();
        }

        return new Key($key, $username, $this->client->ttl($key));
    }

    public function add(Key $key): void
    {
        $this->client->setex((string) $key, $key->ttl(), $key->username());
    }

    public function remove(string $key): void
    {
        $this->client->del([$key]);
    }
}
