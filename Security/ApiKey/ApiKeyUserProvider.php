<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\ApiKey;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface ApiKeyUserProvider extends UserProviderInterface
{
    /**
     * @throws InvalidApiKey
     */
    public function loadUserByApiKey(string $apiKey): UserInterface;
}
