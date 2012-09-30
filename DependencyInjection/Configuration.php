<?php

namespace ERD\SearchBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('erd_search');

        $rootNode
            ->children()
                //can set these separate use_doctrine_* keys manually
                ->booleanNode('use_doctrine_events')->defaultValue(false)->end()
                ->booleanNode('use_doctrine_entities')->defaultValue(false)->end()
                
                /** @todo make this so you can just use_doctrine to true and it'll set both of the above to true */
            ->end()
        ;

        return $treeBuilder;
    }
}