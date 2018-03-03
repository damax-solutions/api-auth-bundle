<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

final class ApiUser implements UserInterface
{
    private const DEFAULT_ROLES = ['ROLE_API'];

    private $username;
    private $roles;

    public function __construct(string $username, array $roles = self::DEFAULT_ROLES)
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): string
    {
        return '';
    }

    public function eraseCredentials(): void
    {
    }
}
