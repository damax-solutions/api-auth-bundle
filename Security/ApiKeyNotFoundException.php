<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiKeyNotFoundException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Api key could not be found.';
    }
}
