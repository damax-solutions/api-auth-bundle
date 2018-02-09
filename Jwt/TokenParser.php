<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt;

interface TokenParser
{
    public function isValid(string $jwt): bool;

    public function parse(string $jwt): Token;
}
