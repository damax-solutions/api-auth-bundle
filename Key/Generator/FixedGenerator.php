<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Generator;

class FixedGenerator implements Generator
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function generateKey(): string
    {
        return $this->key;
    }
}
