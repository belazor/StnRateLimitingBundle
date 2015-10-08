<?php

namespace Stn\RateLimitingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stn_rate_limiting');

        $rootNode
            ->children()
                ->booleanNode('enable')
                    ->defaultTrue()
                ->end()
                ->integerNode('limit')
                    ->isRequired()
                    ->min(0)
                ->end()
                ->integerNode('ttl')
                    ->isRequired()
                    ->min(1)
                ->end()
                ->scalarNode('key_prefix')
                    ->defaultValue('RL')
                ->end()
                ->integerNode('key_length')
                    ->defaultValue(8)
                    ->min(4)
                ->end()
                ->arrayNode('client')
                    ->children()
                        ->scalarNode("dsn")
                            ->isRequired()
                            ->defaultValue('tcp://127.0.0.1:6379')
                        ->end()
                        ->scalarNode("pass")
                            ->defaultValue(null)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
