<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security;

use Damax\Bundle\ApiAuthBundle\Security\ApiKeyNotFoundException;
use Damax\Bundle\ApiAuthBundle\Security\User;
use Damax\Bundle\ApiAuthBundle\Security\UserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\User as SecurityUser;

class UserProviderTest extends TestCase
{
    /**
     * @var UserProvider
     */
    private $userProvider;

    protected function setUp()
    {
        $this->userProvider = new UserProvider(['foo' => 'ABC', 'bar' => 'XYZ']);
    }

    /**
     * @test
     */
    public function it_supports_class()
    {
        $this->assertTrue($this->userProvider->supportsClass(User::class));
        $this->assertFalse($this->userProvider->supportsClass(SecurityUser::class));
    }

    /**
     * @test
     */
    public function it_loads_user()
    {
        $user = $this->userProvider->loadUserByUsername('foo');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('foo', $user->getUsername());

        $user = $this->userProvider->loadUserByUsername('bar');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('bar', $user->getUsername());
    }

    /**
     * @test
     */
    public function it_loads_user_by_api_key()
    {
        $user = $this->userProvider->loadUserByApiKey('ABC');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('foo', $user->getUsername());

        $user = $this->userProvider->loadUserByApiKey('XYZ');

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('bar', $user->getUsername());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_loading_user_with_unregistered_api_key()
    {
        $this->expectException(ApiKeyNotFoundException::class);

        $this->userProvider->loadUserByApiKey('123');
    }

    /**
     * @test
     */
    public function it_throws_exception_on_refreshing_user()
    {
        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Provider "Damax\Bundle\ApiAuthBundle\Security\UserProvider" must be configured as stateless.');

        $this->userProvider->refreshUser(new User('foo'));
    }
}
