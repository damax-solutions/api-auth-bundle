<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const SIGNER_SYMMETRIC = 'symmetric';
    const SIGNER_ASYMMETRIC = 'asymmetric';

    private const SYMMETRIC_ALGOS = ['HS256', 'HS384', 'HS512'];
    private const ASYMMETRIC_ALGOS = ['RS256', 'RS384', 'RS512', 'ES256', 'ES384', 'ES512'];

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('damax_api_auth');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->apiKeyNode('api_key'))
                ->append($this->jwtNode('jwt'))
                ->booleanNode('format_exceptions')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function apiKeyNode(string $name): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition($name))
            ->canBeEnabled()
            ->beforeNormalization()
                ->ifTrue(function (array $config): bool {
                    return !isset($config['tokens']);
                })
                ->then(function (array $config): array {
                    $enabled = $config['enabled'];

                    unset($config['enabled']);

                    return ['enabled' => $enabled, 'tokens' => $config];
                })
            ->end()
            ->children()
                ->arrayNode('tokens')
                    ->useAttributeAsKey(true)
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->isRequired()->end()
                ->end()
                ->append($this->extractorsNode('extractors', [
                    [
                        'type' => 'header',
                        'name' => 'Authorization',
                        'prefix' => 'Token',
                    ],
                ]))
            ->end()
        ;
    }

    private function jwtNode(string $name): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition($name))
            ->canBeEnabled()
            ->children()
                ->append($this->extractorsNode('extractors', [
                    [
                        'type' => 'header',
                        'name' => 'Authorization',
                        'prefix' => 'Bearer',
                    ],
                ]))
                ->scalarNode('identity_claim')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('builder')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('issuer')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('audience')
                            ->cannotBeEmpty()
                        ->end()
                        ->integerNode('ttl')
                            ->defaultValue(3600)
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('parser')
                    ->children()
                        ->arrayNode('issuers')
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')
                                ->isRequired()
                            ->end()
                        ->end()
                        ->scalarNode('audience')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('signer')
                    ->isRequired()
                    ->beforeNormalization()
                        ->ifTrue(function (?array $config): bool {
                            $type = $config['type'] ?? self::SIGNER_SYMMETRIC;

                            return self::SIGNER_ASYMMETRIC === $type;
                        })
                        ->then(function (array $config): array {
                            if (isset($config['signing_key'])) {
                                $config['signing_key'] = 'file://' . $config['signing_key'];
                            }

                            if (isset($config['verification_key'])) {
                                $config['verification_key'] = 'file://' . $config['verification_key'];
                            }

                            if (!isset($config['algorithm'])) {
                                $config['algorithm'] = self::ASYMMETRIC_ALGOS[0];
                            }

                            return $config;
                        })
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $config): bool {
                            return self::SIGNER_ASYMMETRIC === $config['type'] && empty($config['verification_key']);
                        })
                        ->thenInvalid('Verification key must be specified for "asymmetric" signer.')
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $config): bool {
                            return self::SIGNER_SYMMETRIC === $config['type'] && !in_array($config['algorithm'], self::SYMMETRIC_ALGOS);
                        })
                        ->thenInvalid('HMAC algorithm must be specified for "symmetric" signer.')
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $config): bool {
                            return self::SIGNER_ASYMMETRIC === $config['type'] && !in_array($config['algorithm'], self::ASYMMETRIC_ALGOS);
                        })
                        ->thenInvalid('RSA or ECDSA algorithm must be specified for "asymmetric" signer.')
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $config): bool {
                            if (self::SIGNER_SYMMETRIC === $config['type']) {
                                return false;
                            }

                            return !is_readable($config['signing_key']) || !is_readable($config['verification_key']);
                        })
                        ->thenInvalid('Signing and/or verification key is not readable.')
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $config): bool {
                            return self::SIGNER_SYMMETRIC === $config['type'] && !empty($config['verification_key']);
                        })
                        ->thenInvalid('Verification key must no be specified for "symmetric" signer.')
                    ->end()
                    ->validate()
                        ->ifTrue(function (array $config): bool {
                            return self::SIGNER_SYMMETRIC === $config['type'] && !empty($config['passphrase']);
                        })
                        ->thenInvalid('Passphrase must not be specified for "symmetric" signer.')
                    ->end()
                    ->children()
                        ->enumNode('type')
                            ->values(['symmetric', 'asymmetric'])
                            ->defaultValue('symmetric')
                        ->end()
                        ->enumNode('algorithm')
                            ->values(array_merge(self::SYMMETRIC_ALGOS, self::ASYMMETRIC_ALGOS))
                            ->defaultValue(self::SYMMETRIC_ALGOS[0])
                        ->end()
                        ->scalarNode('signing_key')
                            ->isRequired()
                        ->end()
                        ->scalarNode('verification_key')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('passphrase')
                            ->cannotBeEmpty()
                            ->defaultValue('')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function extractorsNode(string $name, array $defaults): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition($name))
            ->prototype('array')
                ->children()
                    ->enumNode('type')
                        ->isRequired()
                        ->values(['header', 'query', 'cookie'])
                    ->end()
                    ->scalarNode('name')
                        ->isRequired()
                    ->end()
                    ->scalarNode('prefix')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
            ->defaultValue($defaults)
        ;
    }
}
