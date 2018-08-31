<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\ApiKey;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class InvalidApiKey extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Invalid api key.';
    }
}
