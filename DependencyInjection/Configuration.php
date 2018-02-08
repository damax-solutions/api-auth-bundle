<?php

declare(strict_types=1);

namespace Damax\Bundle\ApiAuthBundle\DependencyInjection;

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
                ->arrayNode('tokens')
                    ->useAttributeAsKey(true)
                    ->requiresAtLeastOneElement()
                    ->prototype('scalar')->isRequired()->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
