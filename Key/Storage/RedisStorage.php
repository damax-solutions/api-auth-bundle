<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;
use Predis\ClientInterface;

final class RedisStorage implements Storage
{
    private $client;
    private $prefix;

    public function __construct(ClientInterface $client, string $prefix = '')
    {
        $this->client = $client;
        $this->prefix = $prefix;
    }

    public function has(string $key): bool
    {
        $storageKey = $this->storageKey($key);

        return (bool) $this->client->exists($storageKey);
    }

    public function get(string $key): Key
    {
        $storageKey = $this->storageKey($key);

        if (null === $identity = $this->client->get($storageKey)) {
            throw new KeyNotFound();
        }

        return new Key($key, $identity, $this->client->ttl($storageKey));
    }

    public function add(Key $key): void
    {
        $storageKey = $this->storageKey((string) $key);

        $this->client->setex($storageKey, $key->ttl(), $key->identity());
    }

    public function remove(string $key): void
    {
        $storageKey = $this->storageKey($key);

        $this->client->del([$storageKey]);
    }

    private function storageKey(string $key): string
    {
        return $this->prefix . $key;
    }
}
