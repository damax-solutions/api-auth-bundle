<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims\OrganizationClaims;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\User;

class OrganizationClaimsTest extends TestCase
{
    /**
     * @test
     */
    public function it_resolves_no_claims()
    {
        $user = new User('john.doe@domain.abc', 'qwerty');

        $this->assertEmpty((new OrganizationClaims())->resolve($user));
    }

    /**
     * @test
     */
    public function it_resolves_claims()
    {
        $user = new User('john.doe@domain.abc', 'qwerty');

        $claims = (new OrganizationClaims('github', 'app'))->resolve($user);

        $this->assertEquals(['iss' => 'github', 'aud' => 'app'], $claims);
    }
}
