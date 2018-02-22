<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Claims;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims\TimestampClaims;
use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\User;

class TimestampClaimsTest extends TestCase
{
    /**
     * @test
     */
    public function it_resolves_claims()
    {
        $user = new User('john.doe@domain.abc', 'qwerty');

        $claims = (new TimestampClaims(new FrozenClock(new DateTimeImmutable('2018-02-09 06:10:00')), 3600))->resolve($user);

        $this->assertEquals([
            'exp' => new DateTimeImmutable('2018-02-09 07:10:00'),
            'iat' => new DateTimeImmutable('2018-02-09 06:10:00'),
            'nbf' => new DateTimeImmutable('2018-02-09 06:10:00'),
        ], $claims);
    }
}
