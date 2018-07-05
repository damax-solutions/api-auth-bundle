<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\Extractor\ChainExtractor;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\ClaimsCollector;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\OrganizationClaims;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\SecurityClaims;
use Damax\Bundle\ApiAuthBundle\Jwt\Claims\TimestampClaims;
use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Builder;
use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Parser;
use Damax\Bundle\ApiAuthBundle\Jwt\TokenBuilder;
use Damax\Bundle\ApiAuthBundle\Key\Generator\Generator;
use Damax\Bundle\ApiAuthBundle\Key\Storage\ChainStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\DoctrineStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\DummyStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\InMemoryStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Reader;
use Damax\Bundle\ApiAuthBundle\Key\Storage\RedisStorage;
use Damax\Bundle\ApiAuthBundle\Key\Storage\Writer;
use Damax\Bundle\ApiAuthBundle\Listener\ExceptionListener;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\Authenticator as ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\StorageUserProvider;
use Damax\Bundle\ApiAuthBundle\Security\Jwt\AuthenticationHandler;
use Damax\Bundle\ApiAuthBundle\Security\Jwt\Authenticator as JwtAuthenticator;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Signer\Key;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class DamaxApiAuthExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        if ($config['api_key']['enabled']) {
            $this->configureApiKey($config['api_key'], $container);
        }

        if ($config['jwt']['enabled']) {
            $this->configureJwt($config['jwt'], $container);
        }

        if ($config['format_exceptions']['enabled']) {
            $this->configureExceptions($config['format_exceptions'], $container);
        }
    }

    private function configureApiKey(array $config, ContainerBuilder $container): self
    {
        $extractors = $this->configureExtractors($config['extractors']);

        // User provider.
        $container->autowire('damax.api_auth.api_key.user_provider', StorageUserProvider::class);

        // Authenticator.
        $container
            ->register('damax.api_auth.api_key.authenticator', ApiKeyAuthenticator::class)
            ->addArgument($extractors)
        ;

        // Key generator.
        $container
            ->register(Generator::class)
            ->setClass(sprintf('Damax\\Bundle\\ApiAuthBundle\\Key\\Generator\\%sGenerator', ucfirst($config['generator'])))
        ;

        return $this->configureKeyStorage($config['storage'], $container);
    }

    private function configureJwt(array $config, ContainerBuilder $container): self
    {
        $signer = $this->configureJwtSigner($config['signer']);

        $clock = new Definition(SystemClock::class);

        $configuration = (new Definition(JwtConfiguration::class))
            ->setFactory(JwtConfiguration::class . '::forSymmetricSigner')
            ->addArgument($signer)
            ->addArgument(new Definition(Key::class, [
                $config['signer']['signing_key'],
                $config['signer']['passphrase'],
            ]))
        ;

        if (Configuration::SIGNER_ASYMMETRIC === $config['signer']['type']) {
            $configuration
                ->setFactory(JwtConfiguration::class . '::forAsymmetricSigner')
                ->addArgument(new Definition(Key::class, [
                    $config['signer']['verification_key'],
                ]))
            ;
        }

        $parser = (new Definition(Parser::class))
            ->addArgument($configuration)
            ->addArgument($clock)
            ->addArgument($config['parser']['issuers'] ?? null)
            ->addArgument($config['parser']['audience'] ?? null)
        ;

        $claims = $this->configureJwtClaims($config['builder'], $clock, $container);

        $container
            ->register(TokenBuilder::class, Builder::class)
            ->addArgument($configuration)
            ->addArgument($claims)
        ;

        $extractors = $this->configureExtractors($config['extractors']);

        // Authenticator.
        $container
            ->register('damax.api_auth.jwt.authenticator', JwtAuthenticator::class)
            ->addArgument($extractors)
            ->addArgument($parser)
            ->addArgument($config['identity_claim'] ?? null)
        ;

        // Handler.
        $container->autowire('damax.api_auth.jwt.handler', AuthenticationHandler::class);

        return $this;
    }

    private function configureExceptions(array $config, ContainerBuilder $container): self
    {
        $matcher = (new Definition(RequestMatcher::class))->addArgument($config['path'] ?? null);

        $container
            ->autowire(ExceptionListener::class)
            ->setArgument(1, $matcher)
            ->addTag('kernel.event_listener', ['event' => 'kernel.exception', 'method' => 'onKernelException'])
        ;

        return $this;
    }

    private function configureJwtClaims(array $config, Definition $clock, ContainerBuilder $container): Definition
    {
        // Default claims.
        $container
            ->register(TimestampClaims::class)
            ->addArgument($clock)
            ->addArgument($config['ttl'])
            ->addTag('damax.api_auth.jwt_claims')
        ;
        $container
            ->register(OrganizationClaims::class)
            ->addArgument($config['issuer'] ?? null)
            ->addArgument($config['audience'] ?? null)
            ->addTag('damax.api_auth.jwt_claims')
        ;
        $container
            ->register(SecurityClaims::class)
            ->addTag('damax.api_auth.jwt_claims')
        ;

        $container->setAlias(Claims::class, ClaimsCollector::class);

        return $container
            ->register(ClaimsCollector::class)
            ->addArgument(new TaggedIteratorArgument('damax.api_auth.jwt_claims'))
        ;
    }

    private function configureJwtSigner(array $config): Definition
    {
        $dirs = ['HS' => 'Hmac', 'RS' => 'Rsa', 'ES' => 'Ecdsa'];
        $algo = $config['algorithm'];

        return new Definition('Lcobucci\\JWT\\Signer\\' . $dirs[substr($algo, 0, 2)] . '\\Sha' . substr($algo, 2));
    }

    private function configureExtractors(array $config): Definition
    {
        $extractors = [];

        foreach ($config as $item) {
            $className = sprintf('Damax\\Bundle\\ApiAuthBundle\\Extractor\\%sExtractor', ucfirst($item['type']));

            $extractors[] = (new Definition($className))
                ->setArgument(0, $item['name'])
                ->setArgument(1, $item['prefix'] ?? null)
            ;
        }

        return new Definition(ChainExtractor::class, [$extractors]);
    }

    private function configureKeyStorage(array $config, ContainerBuilder $container): self
    {
        $drivers = [];

        // Default writable storage.
        $container->register(Writer::class, DummyStorage::class);

        foreach ($config as $item) {
            $drivers[] = $driver = new Definition();

            if (Configuration::STORAGE_REDIS === $item['type']) {
                $driver
                    ->setClass(RedisStorage::class)
                    ->addArgument(new Reference($item['redis_client_id']))
                    ->addArgument($item['key_prefix'] ?? '')
                ;
            } elseif (Configuration::STORAGE_DOCTRINE === $item['type']) {
                $driver
                    ->setClass(DoctrineStorage::class)
                    ->addArgument(new Reference($item['doctrine_connection_id']))
                    ->addArgument($item['table_name'])
                    ->addArgument($item['fields'] ?? [])
                ;
            } else {
                $driver
                    ->setClass(InMemoryStorage::class)
                    ->addArgument($item['tokens'])
                ;
            }

            if ($item['writable']) {
                $container->setDefinition(Writer::class, $driver);
            }
        }

        $container
            ->register(Reader::class, ChainStorage::class)
            ->addArgument($drivers)
        ;

        return $this;
    }
}
