<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security\ApiKey;

use Damax\Bundle\ApiAuthBundle\Key\Storage\KeyNotFound;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Reader as Storage;
use Damax\Bundle\ApiAuthBundle\Security\ApiUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

final class StorageUserProvider implements ApiKeyUserProvider
{
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function supportsClass($class): bool
    {
        return ApiUser::class === $class;
    }

    public function loadUserByUsername($username): UserInterface
    {
        return new ApiUser($username);
    }

    public function loadUserByApiKey(string $apiKey): UserInterface
    {
        try {
            $key = $this->storage->get($apiKey);
        } catch (KeyNotFound $e) {
            throw new InvalidApiKey();
        }

        if ($key->expired()) {
            throw new InvalidApiKey();
        }

        return $this->loadUserByUsername($key->identity());
    }

    /**
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        throw new UnsupportedUserException(sprintf('Provider "%s" must be configured as stateless.', __CLASS__));
    }
}
