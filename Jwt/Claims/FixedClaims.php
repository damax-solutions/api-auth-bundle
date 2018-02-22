<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Symfony\Component\Security\Core\User\UserInterface;

class FixedClaims implements Claims
{
    private $claims;

    public function __construct(array $claims)
    {
        $this->claims = $claims;
    }

    public function resolve(UserInterface $user): array
    {
        return $this->claims;
    }
}
