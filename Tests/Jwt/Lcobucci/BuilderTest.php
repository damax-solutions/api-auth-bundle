<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt\Lcobucci;

use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Builder;
use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Signer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Role\Role;
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
        $clock = new FrozenClock(new DateTimeImmutable('2018-02-09 06:10:00'));

        $this->config = JwtConfiguration::forSymmetricSigner(new Signer\None(), new Signer\Key(''));
        $this->builder = new Builder($this->config, $clock, 3600, 'github', 'app');
    }

    /**
     * @test
     */
    public function it_builds_jwt_string()
    {
        $user = new User('john.doe@domain.abc', 'qwerty', ['ROLE_USER', new Role('ROLE_ADMIN')]);

        $jwtToken = $this->config->getParser()->parse($this->builder->fromUser($user));

        $this->assertEquals([
            'sub' => 'john.doe@domain.abc',
            'iss' => 'github',
            'aud' => ['app'],
            'exp' => new DateTimeImmutable('2018-02-09 07:10:00'),
            'iat' => new DateTimeImmutable('2018-02-09 06:10:00'),
            'nbf' => new DateTimeImmutable('2018-02-09 06:10:00'),
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
        ], $jwtToken->claims()->all());
    }
}
