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
use Damax\Bundle\ApiAuthBundle\Listener\ExceptionListener;
use Damax\Bundle\ApiAuthBundle\Request\RequestMatcher;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\Authenticator as ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\ApiKey\TokenUserProvider;
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
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DamaxApiAuthExtension extends ConfigurableExtension
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
        $container
            ->register('damax.api_auth.api_key.user_provider', TokenUserProvider::class)
            ->addArgument($config['tokens'])
        ;

        // Authenticator.
        $container
            ->register('damax.api_auth.api_key.authenticator', ApiKeyAuthenticator::class)
            ->addArgument($extractors)
        ;

        return $this;
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
        $container
            ->register('damax.api_auth.jwt.handler', AuthenticationHandler::class)
            ->setAutowired(true)
        ;

        return $this;
    }

    private function configureExceptions(array $config, ContainerBuilder $container): self
    {
        $container
            ->register(ExceptionListener::class)
            ->setAutowired(true)
            ->addTag('kernel.event_listener', ['event' => 'kernel.exception', 'method' => 'onKernelException'])
            ->setArgument(1, new Definition(RequestMatcher::class, [$config['base_url']]))
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
}
