<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims\ClaimsCollector;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\FixedClaims;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\User;

class ClaimsCollectorTest extends TestCase
{
    /**
     * @test
     */
    public function it_resolves_claims()
    {
        $user = new User('john.doe@domain.abc', 'qwerty');

        $collector = new ClaimsCollector([
            new FixedClaims(['foo' => 'bar', 'baz' => 'qux']),
            new FixedClaims(['abc' => 'xyz', 'foo' => 'foo']),
        ]);

        $this->assertEquals(['foo' => 'foo', 'baz' => 'qux', 'abc' => 'xyz'], $collector->resolve($user));
    }
}
