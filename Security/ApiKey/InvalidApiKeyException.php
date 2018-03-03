<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\ApiKey;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class InvalidApiKeyException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Invalid api key.';
    }
}
