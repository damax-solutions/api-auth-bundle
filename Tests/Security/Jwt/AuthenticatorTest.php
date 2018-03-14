<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security\Jwt;

use Damax\Bundle\ApiAuthBundle\Extractor\Extractor;
use Damax\Bundle\ApiAuthBundle\Jwt\Token;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenParser;
use Damax\Bundle\ApiAuthBundle\Security\Jwt\Authenticator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthenticatorTest extends TestCase
{
    /**
     * @var Extractor|MockObject
     */
    private $extractor;

    /**
     * @var TokenParser|MockObject
     */
    private $tokenParser;

    /**
     * @var JwtAuthenticator
     */
    private $authenticator;

    protected function setUp()
    {
        $this->extractor = $this->createMock(Extractor::class);
        $this->tokenParser = $this->createMock(TokenParser::class);
        $this->authenticator = new Authenticator($this->extractor, $this->tokenParser, 'username');
    }

    /**
     * @test
     */
    public function it_checks_credentials()
    {
        /** @var UserInterface $user */
        $user = $this->createMock(UserInterface::class);

        $this->tokenParser
            ->expects($this->once())
            ->method('isValid')
            ->with('ABC')
            ->willReturn(true)
        ;

        $this->assertTrue($this->authenticator->checkCredentials('ABC', $user));
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_identity_field()
    {
        $this->tokenParser
            ->expects($this->once())
            ->method('parse')
            ->with('ABC')
            ->willReturn(Token::fromClaims(['username' => 'john.doe@domain.abc']))
        ;

        /** @var UserInterface $user */
        $user = $this->createMock(UserInterface::class);

        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('john.doe@domain.abc')
            ->willReturn($user)
        ;

        $this->assertSame($user, $this->authenticator->getUser('ABC', $userProvider));
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_subject_field()
    {
        $this->tokenParser
            ->expects($this->once())
            ->method('parse')
            ->with('ABC')
            ->willReturn(Token::fromClaims(['sub' => 'john.doe@domain.abc']))
        ;

        /** @var UserInterface $user */
        $user = $this->createMock(UserInterface::class);

        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('john.doe@domain.abc')
            ->willReturn($user)
        ;

        $this->assertSame($user, (new Authenticator($this->extractor, $this->tokenParser))->getUser('ABC', $userProvider));
    }

    /**
     * @test
     */
    public function it_throws_exception_when_retrieving_user_with_unregistered_claim()
    {
        $this->tokenParser
            ->expects($this->once())
            ->method('parse')
            ->with('ABC')
            ->willReturn(Token::fromClaims([]))
        ;

        /** @var UserProviderInterface|MockObject $userProvider */
        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider
            ->expects($this->never())
            ->method('loadUserByUsername')
        ;

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Username could not be identified.');

        $this->authenticator->getUser('ABC', $userProvider);
    }
}
