<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\ApiKey;

use Damax\Bundle\ApiAuthBundle\Security\AbstractAuthenticator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class Authenticator extends AbstractAuthenticator
{
    public function getUser($credentials, UserProviderInterface $provider): UserInterface
    {
        if ($provider instanceof ApiKeyUserProvider) {
            return $provider->loadUserByApiKey($credentials);
        }

        return $provider->loadUserByUsername($credentials);
    }
}
