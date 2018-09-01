<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security\Jwt;

use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Damax\Bundle\ApiAuthBundle\Security\JsonResponseFactory;
use Damax\Bundle\ApiAuthBundle\Security\Jwt\AuthenticationHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\User;

class AuthenticationHandlerTest extends TestCase
{
    /**
     * @var TokenBuilder|MockObject
     */
    private $builder;

    /**
     * @var AuthenticationHandler
     */
    private $handler;

    protected function setUp()
    {
        $this->builder = $this->createMock(TokenBuilder::class);
        $this->handler = new AuthenticationHandler($this->builder, new JsonResponseFactory());
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
    public function it_fails_to_handle_successful_request()
    {
        $token = new UsernamePasswordToken('anon.', 'qwerty', 'main');

        $this->expectException(UnsupportedUserException::class);

        $this->handler->onAuthenticationSuccess(new Request(), $token);
    }

    /**
     * @test
     */
    public function it_handles_failure_request()
    {
        $response = $this->handler->onAuthenticationFailure(new Request(), new AuthenticationException('Invalid username.'));

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(json_encode(['error' => ['code' => 401, 'message' => 'Unauthorized']]), $response->getContent());
    }
}
