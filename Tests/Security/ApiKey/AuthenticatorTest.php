<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security\ApiKey;

use Damax\Bundle\ApiAuthBundle\Extractor\Extractor;
use Damax\Bundle\ApiAuthBundle\Security\AbstractAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\Authenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\TokenUserProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthenticatorTest extends TestCase
{
    /**
     * @var AbstractAuthenticator
     */
    private $authenticator;

    protected function setUp()
    {
        /** @var Extractor $extractor */
        $extractor = $this->createMock(Extractor::class);

        $this->authenticator = new Authenticator($extractor);
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_api_key()
    {
        $user = $this->authenticator->getUser('ABC', new TokenUserProvider(['user' => 'ABC']));

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

        $this->assertSame($user, $this->authenticator->getUser('ABC', $provider));
    }
}
