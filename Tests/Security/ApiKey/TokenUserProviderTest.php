<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security\ApiKey;

use Damax\Bundle\ApiAuthBundle\Security\ApiKey\InvalidApiKeyException;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\TokenUserProvider;
use Damax\Bundle\ApiAuthBundle\Security\ApiUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\User as SecurityUser;

class TokenUserProviderTest extends TestCase
{
    /**
     * @var TokenUserProvider
     */
    private $userProvider;

    protected function setUp()
    {
        $this->userProvider = new TokenUserProvider(['foo' => 'ABC', 'bar' => 'XYZ']);
    }

    /**
     * @test
     */
    public function it_supports_class()
    {
        $this->assertTrue($this->userProvider->supportsClass(ApiUser::class));
        $this->assertFalse($this->userProvider->supportsClass(SecurityUser::class));
    }

    /**
     * @test
     */
    public function it_loads_user()
    {
        $user = $this->userProvider->loadUserByUsername('foo');

        $this->assertInstanceOf(ApiUser::class, $user);
        $this->assertEquals('foo', $user->getUsername());

        $user = $this->userProvider->loadUserByUsername('bar');

        $this->assertInstanceOf(ApiUser::class, $user);
        $this->assertEquals('bar', $user->getUsername());
    }

    /**
     * @test
     */
    public function it_loads_user_by_api_key()
    {
        $user = $this->userProvider->loadUserByApiKey('ABC');

        $this->assertInstanceOf(ApiUser::class, $user);
        $this->assertEquals('foo', $user->getUsername());

        $user = $this->userProvider->loadUserByApiKey('XYZ');

        $this->assertInstanceOf(ApiUser::class, $user);
        $this->assertEquals('bar', $user->getUsername());
    }

    /**
     * @test
     */
    public function it_throws_exception_when_loading_user_with_unregistered_api_key()
    {
        $this->expectException(InvalidApiKeyException::class);

        $this->userProvider->loadUserByApiKey('123');
    }

    /**
     * @test
     */
    public function it_throws_exception_on_refreshing_user()
    {
        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage('Provider "Damax\Bundle\ApiAuthBundle\Security\ApiKey\TokenUserProvider" must be configured as stateless.');

        $this->userProvider->refreshUser(new ApiUser('foo'));
    }
}
