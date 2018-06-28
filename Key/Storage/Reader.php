<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;

interface Reader
{
    public function has(string $key): bool;

    /**
     * @throws KeyNotFoundException
     */
    public function get(string $key): Key;
}
