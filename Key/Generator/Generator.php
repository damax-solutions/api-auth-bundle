<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Key\Generator;

interface Generator
{
    public function generateKey(): string;
}
