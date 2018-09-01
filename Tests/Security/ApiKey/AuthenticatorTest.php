<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\Security\ApiKey;

use Damax\Bundle\ApiAuthBundle\Extractor\Extractor;
use Damax\Bundle\ApiAuthBundle\Security\AbstractAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\ApiKeyUserProvider;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\Authenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiUser;
use Damax\Bundle\ApiAuthBundle\Security\ResponseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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

        /** @var ResponseFactory $responseFactory */
        $responseFactory = $this->createMock(ResponseFactory::class);

        $this->authenticator = new Authenticator($extractor, $responseFactory);
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_api_key()
    {
        /** @var MockObject|ApiKeyUserProvider $provider */
        $provider = $this->createMock(ApiKeyUserProvider::class);

        $provider
            ->expects($this->once())
            ->method('loadUserByApiKey')
            ->with('XYZ')
            ->willReturn($user = new ApiUser('john.doe'))
        ;

        $this->assertSame($user, $this->authenticator->getUser('XYZ', $provider));
    }

    /**
     * @test
     */
    public function it_retrieves_user_by_username()
    {
        /** @var UserInterface $user */
        $user = $this->createMock(UserInterface::class);

        /** @var MockObject|UserProviderInterface $provider */
        $provider = $this->createMock(UserProviderInterface::class);

        $provider
            ->expects($this->once())
            ->method('loadUserByUsername')
            ->with('XYZ')
            ->willReturn($user)
        ;

        $this->assertSame($user, $this->authenticator->getUser('XYZ', $provider));
    }
}
