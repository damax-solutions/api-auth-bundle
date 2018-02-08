<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security;

use Damax\Bundle\ApiAuthBundle\Security\Authenticator;
use Damax\Bundle\ApiAuthBundle\Security\UserProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthenticatorTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Authenticator
     */
    private $authenticator;

    protected function setUp()
    {
        $this->request = new Request();
        $this->authenticator = new Authenticator('Authorization');
    }

    /**
     * @test
     */
    public function it_supports_authentication()
    {
        $this->assertFalse($this->authenticator->supports($this->request));

        $this->request->headers->set('Authorization', 'ABC');
        $this->assertFalse($this->authenticator->supports($this->request));

        $this->request->headers->set('Authorization', 'Token ABC');
        $this->assertTrue($this->authenticator->supports($this->request));
    }

    /**
     * @test
     */
    public function it_starts_authentication()
    {
        $response = $this->authenticator->start($this->request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('{"message":"Unauthorized"}', $response->getContent());
    }

    /**
     * @test
     */
    public function it_retrieves_credentials()
    {
        $this->request->headers->set('Authorization', 'Token ABC');
        $this->assertEquals(['token' => 'ABC'], $this->authenticator->getCredentials($this->request));
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_api_key()
    {
        $user = $this->authenticator->getUser(['token' => 'ABC'], new UserProvider(['user' => 'ABC']));

        $this->assertEquals('user', $user->getUsername());
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_username()
    {
        /** @var UserInterface $user */
        $user = $this->createMock(UserInterface::class);

        /** @var UserProviderInterface|PHPUnit_Framework_MockObject_MockObject $provider */
        $provider = $this->createMock(UserProviderInterface::class);

        $provider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('ABC')
            ->willReturn($user)
        ;

        $this->assertSame($user, $this->authenticator->getUser(['token' => 'ABC'], $provider));
    }

    /**
     * @test
     */
    public function it_always_validates_credentials()
    {
        /** @var UserInterface $user */
        $user = $this->createMock(UserInterface::class);

        $this->assertTrue($this->authenticator->checkCredentials('password', $user));
    }

    /**
     * @test
     */
    public function it_allows_authentication()
    {
        /** @var TokenInterface $token */
        $token = $this->createMock(TokenInterface::class);

        $this->assertNull($this->authenticator->onAuthenticationSuccess($this->request, $token, 'main'));
    }

    /**
     * @test
     */
    public function it_denies_authentication()
    {
        $response = $this->authenticator->onAuthenticationFailure(new Request(), new AuthenticationException('Authentication error.'));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('{"message":"Forbidden"}', $response->getContent());
    }

    /**
     * @test
     */
    public function it_does_not_support_remember_me()
    {
        $this->assertFalse($this->authenticator->supportsRememberMe());
    }
}
