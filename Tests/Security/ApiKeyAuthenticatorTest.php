<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security;

use Damax\Bundle\ApiAuthBundle\Extractor\Extractor;
use Damax\Bundle\ApiAuthBundle\Security\ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\Authenticator;
use Damax\Bundle\ApiAuthBundle\Security\UserProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyAuthenticatorTest extends TestCase
{
    /**
     * @var Authenticator
     */
    private $authenticator;

    protected function setUp()
    {
        /** @var Extractor $extractor */
        $extractor = $this->createMock(Extractor::class);

        $this->authenticator = new ApiKeyAuthenticator($extractor);
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_api_key()
    {
        $user = $this->authenticator->getUser('ABC', new UserProvider(['user' => 'ABC']));

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
