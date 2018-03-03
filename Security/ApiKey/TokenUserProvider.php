<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\ApiKey;

use Damax\Bundle\ApiAuthBundle\Security\ApiUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenUserProvider implements ApiKeyUserProvider
{
    private $tokens;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function supportsClass($class): bool
    {
        return ApiUser::class === $class;
    }

    public function loadUserByUsername($username): UserInterface
    {
        return new ApiUser($username);
    }

    public function loadUserByApiKey(string $key): UserInterface
    {
        if (false === $username = array_search($key, $this->tokens)) {
            throw new InvalidApiKeyException();
        }

        return $this->loadUserByUsername($username);
    }

    /**
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException(sprintf('Provider "%s" must be configured as stateless.', __CLASS__));
    }
}
