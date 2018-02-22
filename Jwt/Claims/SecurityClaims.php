<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityClaims implements Claims
{
    public function resolve(UserInterface $user): array
    {
        return [
            self::SUBJECT => $user->getUsername(),
            self::ROLES => array_map([$this, 'roleToString'], $user->getRoles()),
        ];
    }

    private function roleToString($role): string
    {
        $str = strtolower(strval($role instanceof Role ? $role->getRole() : $role));

        return 0 === strpos($str, 'role_') ? substr($str, 5) : $str;
    }
}
