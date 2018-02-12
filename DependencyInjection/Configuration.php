<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('damax_api_auth');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->append($this->apiKeyNode('api_key'))
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
