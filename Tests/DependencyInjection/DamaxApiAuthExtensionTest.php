<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\DependencyInjection\DamaxApiAuthExtension;
use Damax\Bundle\ApiAuthBundle\Extractor\ChainExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\CookieExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\HeaderExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\QueryExtractor;
use Damax\Bundle\ApiAuthBundle\Jwt\LcobucciProvider;
use Damax\Bundle\ApiAuthBundle\Listener\ExceptionListener;
use Damax\Bundle\ApiAuthBundle\Security\ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\JwtAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\UserProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\KernelEvents;

class DamaxApiAuthExtensionTest extends AbstractExtensionTestCase
{
    /**
     * @test
     */
    public function it_registers_api_key_services()
    {
        $this->load([
            'api_key' => [
                'tokens' => [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
                'extractors' => [
                    ['type' => 'header', 'name' => 'X-Authorization', 'prefix' => 'KEY'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('damax.api_auth.api_key.user_provider', UserProvider::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('damax.api_auth.api_key.user_provider', 0, [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);

        $this->assertContainerBuilderHasService('damax.api_auth.api_key.authenticator', ApiKeyAuthenticator::class);

        /** @var Definition $extractors */
        $extractors = $this->container
            ->getDefinition('damax.api_auth.api_key.authenticator')
            ->getArgument(0)
        ;
        $this->assertEquals(ChainExtractor::class, $extractors->getClass());

        /** @var Definition[] $definitions */
        $definitions = $extractors->getArgument(0);

        // Header
        $this->assertEquals(HeaderExtractor::class, $definitions[0]->getClass());
        $this->assertEquals('X-Authorization', $definitions[0]->getArgument(0));
        $this->assertEquals('KEY', $definitions[0]->getArgument(1));

        // Query
        $this->assertEquals(QueryExtractor::class, $definitions[1]->getClass());
        $this->assertEquals('api_key', $definitions[1]->getArgument(0));

        // Cookie
        $this->assertEquals(CookieExtractor::class, $definitions[2]->getClass());
        $this->assertEquals('api_key', $definitions[2]->getArgument(0));

        $this->assertContainerBuilderHasServiceDefinitionWithTag(ExceptionListener::class, 'kernel.event_listener', [
            'event' => KernelEvents::EXCEPTION,
            'method' => 'onKernelException',
        ]);
    }

    /**
     * @test
     */
    public function it_registers_exception_formatting()
    {
        $this->load([]);

        $this->assertContainerBuilderHasServiceDefinitionWithTag(ExceptionListener::class, 'kernel.event_listener', [
            'event' => 'kernel.exception',
            'method' => 'onKernelException',
        ]);
    }

    /**
     * @test
     */
    public function it_does_not_register_exception_formatting()
    {
        $this->load(['format_exceptions' => false]);

        $this->assertEmpty($this->container->getDefinition(ExceptionListener::class)->getTags());
    }

    /**
     * @test
     */
    public function it_registers_jwt_services_with_symmetric_signer()
    {
        $key = tempnam(sys_get_temp_dir(), 'key_');

        $this->load([
            'jwt' => [
                'identity_claim' => 'username',
                'signer' => [
                    'type' => 'asymmetric',
                    'algorithm' => 'RS256',
                    'signing_key' => $key,
                    'verification_key' => $key,
                ],
                'builder' => [
                    'audience' => 'symfony',
                ],
                'parser' => [
                    'issuers' => ['damax', 'damax-api-auth-bundle'],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('damax.api_auth.jwt.authenticator', JwtAuthenticator::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('damax.api_auth.jwt.authenticator', 2, 'username');

        /** @var Definition $extractors */
        $extractors = $this->container
            ->getDefinition('damax.api_auth.jwt.authenticator')
            ->getArgument(0)
        ;
        $this->assertEquals(ChainExtractor::class, $extractors->getClass());

        /** @var Definition[] $definitions */
        $definitions = $extractors->getArgument(0);

        // Header
        $this->assertEquals(HeaderExtractor::class, $definitions[0]->getClass());
        $this->assertEquals('Authorization', $definitions[0]->getArgument(0));
        $this->assertEquals('Bearer', $definitions[0]->getArgument(1));

        /** @var Definition $parser */
        $parser = $this->container
            ->getDefinition('damax.api_auth.jwt.authenticator')
            ->getArgument(1)
        ;
        $this->assertEquals(LcobucciProvider::class, $parser->getClass());
        $this->assertEquals(['damax', 'damax-api-auth-bundle'], $parser->getArgument(2));
        $this->assertEquals('symfony', $parser->getArgument(3));

        /** @var Definition $config */
        $config = $parser->getArgument(0);
        $this->assertEquals(Configuration::class, $config->getClass());
        $this->assertCount(3, $config->getArguments());

        /** @var Definition $signer */
        $signer = $config->getArgument(0);
        $this->assertEquals(Sha256::class, $signer->getClass());

        unlink($key);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new DamaxApiAuthExtension(),
        ];
    }
}
