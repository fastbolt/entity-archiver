<?php

namespace Fastbolt\EntityArchiverBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const ARCHIVE_TABLE_SUFFIX_DEFAULT = '_archive';

    public const ARCHIVING_DATE_FIELD_NAME_DEFAULT = 'archived_at';

    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('entity_archiver');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('table_suffix')->defaultValue(self::ARCHIVE_TABLE_SUFFIX_DEFAULT)->end()
                ->arrayNode('entities')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('entity')->isRequired()->end()
                            ->scalarNode('strategy')->defaultValue('archive')->end()
                            ->booleanNode('addArchivedAt')->defaultTrue()->end()
                            ->scalarNode('archivingDateFieldName')
                                ->defaultValue(self::ARCHIVING_DATE_FIELD_NAME_DEFAULT)->end()
                            ->arrayNode('filters')
                                ->arrayPrototype()
                                    ->scalarPrototype()->end()                          //undefined number of arguments
                                    ->children()
                                        ->scalarNode('type')->isRequired()->end()      //all filters need a type
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('fields')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

         return $treeBuilder;
    }
}
