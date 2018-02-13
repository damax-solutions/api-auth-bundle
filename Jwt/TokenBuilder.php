<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Jwt;

use Symfony\Component\Security\Core\User\UserInterface;

interface TokenBuilder
{
    public function fromUser(UserInterface $user): string;
}
