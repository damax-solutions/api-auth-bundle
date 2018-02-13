<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Jwt;

use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Builder;
use Damax\Bundle\ApiAuthBundle\Security\JwtHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\User;

class JwtHandlerTest extends TestCase
{
    /**
     * @var Builder|PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var JwtHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->builder = $this->createMock(Builder::class);
        $this->handler = new JwtHandler($this->builder);
    }

    /**
     * @test
     */
    public function it_handles_successful_request()
    {
        $user = new User('john.doe@domain.abc', 'qwerty');

        $this->builder
            ->expects($this->once())
            ->method('fromUser')
            ->with($this->identicalTo($user))
            ->willReturn('XYZ')
        ;

        $response = $this->handler->onAuthenticationSuccess(new Request(), new UsernamePasswordToken($user, 'qwerty', 'main'));

        $this->assertEquals(json_encode(['token' => 'XYZ']), $response->getContent());
    }

    /**
     * @test
     */
    public function it_handles_failure_request()
    {
        $response = $this->handler->onAuthenticationFailure(new Request(), new AuthenticationException('Invalid username.'));

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(json_encode(['message' => 'Invalid username.']), $response->getContent());
    }
}
