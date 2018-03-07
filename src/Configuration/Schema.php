<?php
declare(strict_types = 1);

namespace Innmind\Tower\Configuration;

use Symfony\Component\Config\Definition\{
    ConfigurationInterface,
    Builder\TreeBuilder,
};

final class Schema implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('tower');

        $root
            ->children()
                ->arrayNode('exports')
                    ->prototype('scalar')->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('actions')
                    ->prototype('scalar')->end()
                    ->requiresAtLeastOneElement()
                ->end()
                ->arrayNode('neighbours')
                    ->useAttributeAsKey('name')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('url')->end()
                            ->arrayNode('tags')
                                ->prototype('scalar')->end()
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
