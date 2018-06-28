<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\DependencyInjection\DamaxApiAuthExtension;
use Damax\Bundle\ApiAuthBundle\Extractor\ChainExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\CookieExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\HeaderExtractor;
use Damax\Bundle\ApiAuthBundle\Extractor\QueryExtractor;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\OrganizationClaims;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\SecurityClaims;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\TimestampClaims;
use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Builder;
use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Parser;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Damax\Bundle\ApiAuthBundle\Key\Storage\ChainStorage;
use Damax\Bundle\ApiAuthBundle\Listener\ExceptionListener;
use Damax\Bundle\ApiAuthBundle\Request\RequestMatcher;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\Authenticator as ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\StorageUserProvider;
use Damax\Bundle\ApiAuthBundle\Security\Jwt\Authenticator as JwtAuthenticator;
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
                'extractors' => [
                    ['type' => 'header', 'name' => 'X-Authorization', 'prefix' => 'KEY'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
                'storage' => [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('damax.api_auth.api_key.user_provider', StorageUserProvider::class);

        /** @var Definition $drivers */
        $drivers = $this->container
            ->getDefinition('damax.api_auth.api_key.user_provider')
            ->getArgument(0)
        ;
        $this->assertEquals(ChainStorage::class, $drivers->getClass());

        /** @var Definition[] $definitions */
        $definitions = $drivers->getArgument(0);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'qux',
        ], $definitions[0]->getArgument(0));

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
    }

    /**
     * @test
     */
    public function it_registers_exception_formatting()
    {
        $this->load([
            'format_exceptions' => '/api',
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithTag(ExceptionListener::class, 'kernel.event_listener', [
            'event' => KernelEvents::EXCEPTION,
            'method' => 'onKernelException',
        ]);

        /** @var Definition $requestMatcher */
        $requestMatcher = $this->container
            ->getDefinition(ExceptionListener::class)
            ->getArgument(1)
        ;
        $this->assertEquals(RequestMatcher::class, $requestMatcher->getClass());
        $this->assertEquals('/api', $requestMatcher->getArgument(0));
    }

    /**
     * @test
     */
    public function it_does_not_register_exception_formatting()
    {
        $this->load(['format_exceptions' => false]);

        $this->assertContainerBuilderNotHasService(ExceptionListener::class);
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
                'parser' => [
                    'issuers' => ['damax', 'damax-api-auth-bundle'],
                    'audience' => 'symfony',
                ],
                'builder' => [
                    'issuer' => 'damax',
                    'audience' => 'zend',
                    'ttl' => 600,
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
        $this->assertEquals(Parser::class, $parser->getClass());
        $this->assertEquals(['damax', 'damax-api-auth-bundle'], $parser->getArgument(2));
        $this->assertEquals('symfony', $parser->getArgument(3));

        /** @var Definition $config */
        $config = $parser->getArgument(0);
        $this->assertEquals(Configuration::class, $config->getClass());
        $this->assertCount(3, $config->getArguments());

        /** @var Definition $signer */
        $signer = $config->getArgument(0);
        $this->assertEquals(Sha256::class, $signer->getClass());

        $this->assertContainerBuilderHasService('damax.api_auth.jwt.handler');

        // Builder
        $this->assertContainerBuilderHasService(TokenBuilder::class, Builder::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(TokenBuilder::class, 0, $config);

        // Claims
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(TimestampClaims::class, 0);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(TimestampClaims::class, 1, 600);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(TimestampClaims::class, 'damax.api_auth.jwt_claims');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(OrganizationClaims::class, 0, 'damax');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(OrganizationClaims::class, 1, 'zend');
        $this->assertContainerBuilderHasServiceDefinitionWithTag(OrganizationClaims::class, 'damax.api_auth.jwt_claims');
        $this->assertContainerBuilderHasService(SecurityClaims::class);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(SecurityClaims::class, 'damax.api_auth.jwt_claims');

        unlink($key);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new DamaxApiAuthExtension(),
        ];
    }
}
