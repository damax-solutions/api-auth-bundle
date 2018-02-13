<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\DependencyInjection;

use Damax\Bundle\ApiAuthBundle\Extractor\ChainExtractor;
use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Builder;
use Damax\Bundle\ApiAuthBundle\Jwt\Lcobucci\Parser;
use Damax\Bundle\ApiAuthBundle\Listener\ExceptionListener;
use Damax\Bundle\ApiAuthBundle\Security\ApiKeyAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\JwtAuthenticator;
use Damax\Bundle\ApiAuthBundle\Security\JwtHandler;
use Damax\Bundle\ApiAuthBundle\Security\UserProvider;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration as JwtConfiguration;
use Lcobucci\JWT\Signer\Key;
use Symfony\Component\Config\FileLocator;
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

        if ($config['format_exceptions']) {
            $this->configureExceptions($container);
        }
    }

    private function configureApiKey(array $config, ContainerBuilder $container): self
    {
        $extractors = $this->configureExtractors($config['extractors']);

        // User provider.
        $container->setDefinition('damax.api_auth.api_key.user_provider', new Definition(UserProvider::class, [$config['tokens']]));

        // Authenticator.
        $container->setDefinition('damax.api_auth.api_key.authenticator', new Definition(ApiKeyAuthenticator::class, [$extractors]));

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

        $builder = (new Definition(Builder::class))
            ->addArgument($configuration)
            ->addArgument($clock)
            ->addArgument($config['builder']['ttl'])
            ->addArgument($config['builder']['issuer'] ?? null)
            ->addArgument($config['builder']['audience'] ?? null)
        ;

        $extractors = $this->configureExtractors($config['extractors']);

        // Authenticator.
        $container->setDefinition('damax.api_auth.jwt.authenticator', new Definition(JwtAuthenticator::class, [
            $extractors,
            $parser,
            $config['identity_claim'] ?? null,
        ]));

        // Handler.
        $container->setDefinition('damax.api_auth.jwt.handler', new Definition(JwtHandler::class, [
            $builder,
        ]));

        return $this;
    }

    private function configureExceptions(ContainerBuilder $container): self
    {
        $container
            ->getDefinition(ExceptionListener::class)
            ->addTag('kernel.event_listener', ['event' => 'kernel.exception', 'method' => 'onKernelException'])
        ;

        return $this;
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
