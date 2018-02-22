<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims\SecurityClaims;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\User;

class SecurityClaimsTest extends TestCase
{
    /**
     * @test
     */
    public function it_resolves_claims()
    {
        $user = new User('john.doe@domain.abc', 'qwerty', ['ROLE_USER', new Role('ROLE_ADMIN')]);

        $claims = (new SecurityClaims())->resolve($user);

        $this->assertEquals(['sub' => 'john.doe@domain.abc', 'roles' => ['user', 'admin']], $claims);
    }
}
