<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Predis\ClientInterface;

final class RedisStorage implements Storage
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function has(string $key): bool
    {
        return (bool) $this->client->exists($key);
    }

    public function get(string $key): Key
    {
        if (null === $identity = $this->client->get($key)) {
            throw new KeyNotFound();
        }

        return new Key($key, $identity, $this->client->ttl($key));
    }

    public function add(Key $key): void
    {
        $this->client->setex((string) $key, $key->ttl(), $key->identity());
    }

    public function remove(string $key): void
    {
        $this->client->del([$key]);
    }
}
