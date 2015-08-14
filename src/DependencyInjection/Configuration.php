<?php

/**
 * This file is part of the RollerworksNavigationBundle package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\NavigationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var
     */
    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->alias);

        $rootNode
            ->children()
                ->arrayNode('menus')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('template')->defaultNull()->end()
                            ->arrayNode('items')
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end() // use variable as we can't nest to deep
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('breadcrumbs')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('parent')->defaultNull()->end()
                            ->scalarNode('label')->defaultNull()->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                            ->scalarNode('translator_domain')->defaultValue('Breadcrumbs')->end()
                            ->scalarNode('uri')->defaultNull()->end()
                            ->arrayNode('route')
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                    ->booleanNode('absolute')->defaultFalse()->end()
                                    ->arrayNode('parameters')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('service')
                                ->children()
                                    ->scalarNode('id')->cannotBeEmpty()->end()
                                    ->scalarNode('method')->cannotBeEmpty()->end()
                                    ->arrayNode('parameters')
                                        ->prototype('variable')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('expression')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    final public function addItemConfig(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('expression')->defaultNull()->end()
                ->scalarNode('label')->defaultNull()->end()
                ->arrayNode('options')
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end()
                ->end()
                ->scalarNode('translator_domain')->defaultValue('Menus')->end()
                ->scalarNode('uri')->defaultNull()->end()
                ->arrayNode('route')
                    ->children()
                        ->scalarNode('name')->cannotBeEmpty()->end()
                        ->booleanNode('absolute')->defaultFalse()->end()
                        ->arrayNode('parameters')
                            ->useAttributeAsKey('name')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('service')
                    ->children()
                        ->scalarNode('id')->cannotBeEmpty()->end()
                        ->scalarNode('method')->cannotBeEmpty()->end()
                        ->arrayNode('parameters')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('items')
                    ->useAttributeAsKey('name')
                    ->prototype('variable')->end() // use variable as we can't nest to deep
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return !empty($v['service']) && (!empty($v['expression']) || null !== $v['label']); })
                ->thenInvalid('When a "service" or "expression" is set no other configurations should be set for this item.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return !empty($v['route']) && !empty($v['uri']); })
                ->thenInvalid('An item can only have a route or uri, not both.')
            ->end()
            ->validate()
                ->ifTrue(function ($v) { return empty($v['service']) && empty($v['expression']) && null === $v['label']; })
                ->thenInvalid('Missing a value for either "service" or "label" for this item.')
            ->end()
        ;
    }
}
