<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\Tests\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\Command\Storage\AddKeyCommand;
use Damax\Bundle\ApiAuthBundle\Command\Storage\LookupKeyCommand;
use Damax\Bundle\ApiAuthBundle\Command\Storage\RemoveKeyCommand;
use Damax\Bundle\ApiAuthBundle\Controller\TokenController;
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
use Damax\Bundle\ApiAuthBundle\Key\Generator\Generator;
use Damax\Bundle\ApiAuthBundle\Key\Generator\RandomGenerator;
use Damax\Bundle\ApiAuthBundle\Key\Storage\ChainStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\DoctrineStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\InMemoryStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Reader;
use Damax\Bundle\ApiAuthBundle\Key\Storage\RedisStorage;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\Authenticator as ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\StorageUserProvider;
use Damax\Bundle\ApiAuthBundle\Security\JsonResponseFactory;
use Damax\Bundle\ApiAuthBundle\Security\Jwt\Authenticator as JwtAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\ResponseFactory;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DamaxApiAuthExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->container->setParameter('kernel.bundles', []);
    }

    /**
     * @test
     */
    public function it_registers_response_factory()
    {
        $this->load();

        $this->assertContainerBuilderHasService(JsonResponseFactory::class);
        $this->assertContainerBuilderHasAlias(ResponseFactory::class, JsonResponseFactory::class);
    }

    /**
     * @test
     */
    public function it_registers_custom_response_factory()
    {
        $this->load(['response_factory_service_id' => 'factory_service_id']);

        $this->assertContainerBuilderHasService(JsonResponseFactory::class);
        $this->assertContainerBuilderHasAlias(ResponseFactory::class, 'factory_service_id');
    }

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
            ],
        ]);

        $this->assertContainerBuilderHasService(Generator::class, RandomGenerator::class);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Generator::class, 0, 20);
        $this->assertContainerBuilderHasService('damax.api_auth.api_key.user_provider', StorageUserProvider::class);
        $this->assertContainerBuilderHasService('damax.api_auth.api_key.authenticator', ApiKeyAuthenticator::class);
        $this->assertContainerBuilderHasService(Reader::class, ChainStorage::class);

        /** @var Definition $extractors */
        $extractors = $this->container
            ->getDefinition('damax.api_auth.api_key.authenticator')
            ->getArgument(0)
        ;
        $this->assertEquals(ChainExtractor::class, $extractors->getClass());
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('damax.api_auth.api_key.authenticator', 1, ResponseFactory::class);

        // Console
        $this->assertContainerBuilderHasServiceDefinitionWithTag(AddKeyCommand::class, 'console.command');
        $this->assertContainerBuilderHasServiceDefinitionWithTag(RemoveKeyCommand::class, 'console.command');
        $this->assertContainerBuilderHasServiceDefinitionWithTag(LookupKeyCommand::class, 'console.command');
    }

    /**
     * @test
     */
    public function it_registers_key_extractors()
    {
        $this->load([
            'api_key' => [
                'extractors' => [
                    ['type' => 'header', 'name' => 'X-Authorization', 'prefix' => 'KEY'],
                    ['type' => 'query', 'name' => 'api_key'],
                    ['type' => 'cookie', 'name' => 'api_key'],
                ],
            ],
        ]);

        /** @var Definition[] $extractors */
        $extractors = $this->container
            ->getDefinition('damax.api_auth.api_key.authenticator')
            ->getArgument(0)
            ->getArgument(0)
        ;

        // Header
        $this->assertEquals(HeaderExtractor::class, $extractors[0]->getClass());
        $this->assertEquals('X-Authorization', $extractors[0]->getArgument(0));
        $this->assertEquals('KEY', $extractors[0]->getArgument(1));

        // Query
        $this->assertEquals(QueryExtractor::class, $extractors[1]->getClass());
        $this->assertEquals('api_key', $extractors[1]->getArgument(0));

        // Cookie
        $this->assertEquals(CookieExtractor::class, $extractors[2]->getClass());
        $this->assertEquals('api_key', $extractors[2]->getArgument(0));
    }

    /**
     * @test
     */
    public function it_registers_key_storage_drivers()
    {
        $this->load([
            'api_key' => [
                'generator' => ['key_size' => 40],
                'storage' => [
                    [
                        'type' => 'fixed',
                        'tokens' => ['john.doe' => 'ABC', 'jane.doe' => 'XYZ'],
                    ],
                    [
                        'type' => 'redis',
                        'key_prefix' => 'api_',
                    ],
                    [
                        'type' => 'doctrine',
                        'fields' => ['key' => 'id', 'identity' => 'user_id'],
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Generator::class, 0, 40);

        /** @var Definition[] $drivers */
        $drivers = $this->container->getDefinition(Reader::class)->getArgument(0);

        // In memory
        $this->assertEquals(InMemoryStorage::class, $drivers[0]->getClass());
        $this->assertEquals(['john.doe' => 'ABC', 'jane.doe' => 'XYZ'], $drivers[0]->getArgument(0));

        // Redis
        $this->assertEquals(RedisStorage::class, $drivers[1]->getClass());
        $this->assertEquals(new Reference('snc_redis.default'), $drivers[1]->getArgument(0));
        $this->assertEquals('api_', $drivers[1]->getArgument(1));

        // Doctrine
        $this->assertEquals(DoctrineStorage::class, $drivers[2]->getClass());
        $this->assertEquals(new Reference('database_connection'), $drivers[2]->getArgument(0));
        $this->assertEquals('api_key', $drivers[2]->getArgument(1));
        $this->assertEquals(['key' => 'id', 'identity' => 'user_id'], $drivers[2]->getArgument(2));
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
        $this->assertContainerBuilderHasServiceDefinitionWithArgument('damax.api_auth.jwt.authenticator', 3, 'username');

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

        $this->assertContainerBuilderHasServiceDefinitionWithArgument('damax.api_auth.jwt.authenticator', 1, ResponseFactory::class);

        /** @var Definition $parser */
        $parser = $this->container
            ->getDefinition('damax.api_auth.jwt.authenticator')
            ->getArgument(2)
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

        // Controller
        $this->assertContainerBuilderHasServiceDefinitionWithTag(TokenController::class, 'controller.service_arguments');

        unlink($key);
    }

    /**
     * @test
     */
    public function it_configures_nelmio_api_doc_definitions()
    {
        $this->container->setParameter('kernel.bundles', ['NelmioApiDocBundle' => true]);

        $this->load();

        $config = $this->container->getExtensionConfig('nelmio_api_doc')[0];

        $this->assertArrayHasKey('documentation', $config);
        $this->assertArrayHasKey('definitions', $config['documentation']);
        $this->assertArrayHasKey('SecurityLogin', $config['documentation']['definitions']);
        $this->assertArrayHasKey('SecurityLoginResult', $config['documentation']['definitions']);
    }

    protected function getContainerExtensions(): array
    {
        return [
            new DamaxApiAuthExtension(),
        ];
    }
}
