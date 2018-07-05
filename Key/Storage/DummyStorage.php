<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;

/**
 * @codeCoverageIgnore
 */
final class DummyStorage implements Writer
{
    public function add(Key $key): void
    {
    }

    public function remove(string $key): void
    {
    }
}
