<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;

final class ChainStorage implements Reader
{
    /**
     * @var Reader[]
     */
    private $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->addStorage($item);
        }
    }

    public function addStorage(Reader $storage): void
    {
        $this->items[] = $storage;
    }

    public function has(string $key): bool
    {
        foreach ($this->items as $storage) {
            if ($storage->has($key)) {
                return true;
            }
        }

        return false;
    }

    public function get(string $key): Key
    {
        foreach ($this->items as $storage) {
            if ($storage->has($key)) {
                return $storage->get($key);
            }
        }

        throw new KeyNotFound();
    }
}
