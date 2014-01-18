<?php

/*
 * This file is part of the RollerworksNavigationBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
                            ->scalarNode('translator_domain')->defaultValue('Breadcrumbs')->end()
                            ->arrayNode('route')
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
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
                ->scalarNode('translator_domain')->defaultValue('Menus')->end()
                ->arrayNode('route')
                    ->children()
                        ->scalarNode('name')->cannotBeEmpty()->end()
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
                ->ifTrue(function ($v) { return empty($v['service']) && empty($v['expression']) && null === $v['label']; })
                ->thenInvalid('Missing a value for either "service" or "label" for this item.')
            ->end()
        ;
    }
}
