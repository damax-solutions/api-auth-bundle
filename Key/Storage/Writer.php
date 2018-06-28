<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Storage;

use Damax\Bundle\ApiAuthBundle\Key\Key;

interface Writer
{
    public function add(Key $key): void;

    public function remove(string $key): void;
}
