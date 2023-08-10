<?php

namespace Fastbolt\EntityArchiverBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    private const ARCHIVE_TABLE_SUFFIX_DEFAULT = '_archive';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('entity_archiver');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('table_suffix')->defaultValue(self::ARCHIVE_TABLE_SUFFIX_DEFAULT)->end()
                ->arrayNode('entities')
                    ->arrayPrototype()->children()
                        ->scalarNode('strategy')->defaultValue('archive')->end()
                        ->arrayNode('filters') //TODO the following is probably wrong
                            ->arrayPrototype()
                                ->scalarPrototype()->end()                          //undefined number of arguments
                            ->children()
                                ->scalarNode('type')->isRequired()->end()      //all filters need a type
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

         return $treeBuilder;
    }
}
