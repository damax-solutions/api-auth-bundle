<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims\FixedClaims;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\User;

class FixedClaimsTest extends TestCase
{
    /**
     * @test
     */
    public function it_resolves_claims()
    {
        $user = new User('john.doe@domain.abc', 'qwerty');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'qux'], (new FixedClaims(['foo' => 'bar', 'baz' => 'qux']))->resolve($user));
    }
}
