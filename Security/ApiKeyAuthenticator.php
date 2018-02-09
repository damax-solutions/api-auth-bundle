<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticator extends Authenticator
{
    public function getUser($credentials, UserProviderInterface $provider): UserInterface
    {
        if ($provider instanceof ApiKeyUserProvider) {
            return $provider->loadUserByApiKey($credentials);
        }

        return $provider->loadUserByUsername($credentials);
    }
}
