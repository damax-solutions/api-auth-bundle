<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key;

use Damax\Bundle\ApiAuthBundle\Key\Generator\Generator;

final class Factory
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function createKey(string $identity, int $ttl): Key
    {
        return new Key($this->generator->generateKey(), $identity, $ttl);
    }
}
