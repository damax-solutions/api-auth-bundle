<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Lcobucci;

use Damax\Bundle\ApiAuthBundle\Jwt\Claims\FixedClaims;
use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Builder;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Signer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\User;

class BuilderTest extends TestCase
{
    /**
     * @var JwtConfiguration
     */
    private $config;

    /**
     * @var Builder
     */
    private $builder;

    protected function setUp()
    {
        $this->config = JwtConfiguration::forSymmetricSigner(new Signer\None(), new Signer\Key(''));
        $this->builder = new Builder($this->config, new FixedClaims([
            'sub' => 'john.doe@domain.abc',
            'iss' => 'github',
            'aud' => 'app',
            'exp' => new DateTimeImmutable('2018-02-09 07:10:00'),
            'iat' => new DateTimeImmutable('2018-02-09 06:10:00'),
            'nbf' => new DateTimeImmutable('2018-02-09 06:10:00'),
            'jti' => '123',
            'foo' => 'bar',
            'baz' => 'qux',
        ]));
    }

    /**
     * @test
     */
    public function it_builds_jwt_string()
    {
        $user = new User('john.doe@domain.abc', 'qwerty');

        $jwtToken = $this->config->getParser()->parse($this->builder->fromUser($user));

        $this->assertEquals([
            'sub' => 'john.doe@domain.abc',
            'iss' => 'github',
            'aud' => ['app'],
            'exp' => new DateTimeImmutable('2018-02-09 07:10:00'),
            'iat' => new DateTimeImmutable('2018-02-09 06:10:00'),
            'nbf' => new DateTimeImmutable('2018-02-09 06:10:00'),
            'jti' => '123',
            'foo' => 'bar',
            'baz' => 'qux',
        ], $jwtToken->claims()->all());
    }
}
