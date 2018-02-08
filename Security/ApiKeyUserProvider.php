<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface ApiKeyUserProvider extends UserProviderInterface
{
    /**
     * @throws ApiKeyNotFoundException
     */
    public function loadUserByApiKey(string $key): UserInterface;
}
