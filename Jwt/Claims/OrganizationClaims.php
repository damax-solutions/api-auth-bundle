<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Symfony\Component\Security\Core\User\UserInterface;

class OrganizationClaims implements Claims
{
    private $issuer;
    private $audience;

    public function __construct(string $issuer = null, string $audience = null)
    {
        $this->issuer = $issuer;
        $this->audience = $audience;
    }

    public function resolve(UserInterface $user): array
    {
        $data = [];

        if ($this->issuer) {
            $data[self::ISSUER] = $this->issuer;
        }

        if ($this->audience) {
            $data[self::AUDIENCE] = $this->audience;
        }

        return $data;
    }
}
