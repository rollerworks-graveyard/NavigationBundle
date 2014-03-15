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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class NavigationExtension extends Extension
{
    /**
     * @var \Symfony\Component\Config\Definition\NodeInterface
     */
    private static $configTree;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($this->getAlias());
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerMenus($config['menus'], $container);
        $this->registerBreadcrumbs($config['breadcrumbs'], $container);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'rollerworks_navigation';
    }

    /**
     * @param array            $menus
     * @param ContainerBuilder $container
     */
    private function registerMenus(array $menus, ContainerBuilder $container)
    {
        foreach ($menus as $name => $menu) {
            $def = $this->createMenuItem('root', $this->resolveParameters($menu));
            $def->setTags(array('knp_menu.menu' => array(array('alias' => $name))));
            $this->buildMenuDefinition($def, $menu['items']);

            $container->setDefinition('rollerworks_navigation.menu.' . $name, $def);
        }
    }

    /**
     * @param array            $breadcrumbs
     * @param ContainerBuilder $container
     *
     * @throws \RuntimeException
     */
    private function registerBreadcrumbs(array $breadcrumbs, ContainerBuilder $container)
    {
        /** @var Definition[] $finalBreadcrumbs */
        $finalBreadcrumbs = array();

        foreach ($breadcrumbs as $name => $breadcrumb) {
            // Basically what we do is pass trough all the parents
            // And keep track of them, we then reverse them, and loop trough the list
            $methods = array();

            $breadcrumbName = $name;
            $loaded = array();

            while (null !== $breadcrumb) {
                $child = $this->createMenuItemDefinition($name, $breadcrumb);
                $loaded[$name] = true;

                if (is_array($child)) {
                    unset($child['parent']);

                    $methods[] = array('addChild', array($name, $child));
                } else {
                    $methods[] = array('addChild', array($child));
                }

                if (null !== $breadcrumb['parent']) {
                    if (!isset($breadcrumbs[$breadcrumb['parent']])) {
                        throw new \RuntimeException(sprintf('Parent "%s" of breadcrumb "%s" is not registered.', $breadcrumb['parent'], $name));
                    }

                    if (isset($loaded[$breadcrumb['parent']])) {
                        throw new \RuntimeException(sprintf('Circular reference detected with parent of breadcrumb "%s", path: "%s".', $name, implode(' -> ', array_keys($loaded))));
                    }

                    $name = $breadcrumb['parent'];
                    $breadcrumb = $breadcrumbs[$breadcrumb['parent']];

                    continue;
                }

                break;
            }

            // reverse to make the actual child last
            $methods = array_reverse($methods);

            unset($breadcrumb['parent']);

            $finalBreadcrumbs[$breadcrumbName] = $this->createMenuItem('root');
            $finalBreadcrumbs[$breadcrumbName]->setTags(array('knp_menu.menu' => array(array('alias' => $breadcrumbName))));
            $finalBreadcrumbs[$breadcrumbName]->setMethodCalls($methods);
            $container->setDefinition('rollerworks_navigation.breadcrumbs.' . $breadcrumbName, $finalBreadcrumbs[$breadcrumbName]);
        }
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return Definition
     */
    private function createMenuItem($name, array $options = array())
    {
        unset($options['items']);

        $def = new Definition('Knp\Menu\MenuFactory');
        $def->setFactoryService('knp_menu.factory');
        $def->setFactoryMethod('createItem');
        $def->setArguments(array($name, $options));

        return $def;
    }

    /**
     * @param string $name
     * @param array  $item
     *
     * @return Definition|array
     */
    private function createMenuItemDefinition($name, array $item)
    {
        if (isset($item['route']['parameters'])) {
            $item['route']['parameters'] = $this->resolveParameters($item['route']['parameters']);
        }

        if (!empty($item['service'])) {
            $definition = new Definition('stdClass');
            $definition->setFactoryService($item['service']['id']);
            $definition->setFactoryMethod($item['service']['method']);

            if (isset($item['service']['parameters'])) {
                $parameters = $this->resolveParameters($item['service']['parameters']);

                if (!is_array($parameters)) {
                    $definition->setArguments(array($parameters));
                } else {
                    $definition->setArguments($parameters);
                }
            }
        } elseif (!empty($item['expression'])) {
            return new Expression($item['expression']);
        } elseif (!empty($item['items'])) {
            $childItems = $item['items'];

            // Don't pass the items to the factory
            unset($item['items'], $item['expression']);

            $definition = $this->createMenuItem($name, $item);
            $this->buildMenuDefinition($definition, $childItems);
        } else {
            unset($item['items'], $item['expression']);

            $definition = $item;
        }

        return $definition;
    }

    /**
     * @param Definition $definition
     * @param array      $items
     */
    private function buildMenuDefinition(Definition $definition, array $items)
    {
        foreach ($items as $name => $item) {
            $item = $this->validateMenuItemConfig($item);
            $child = $this->createMenuItemDefinition($name, $item);

            if (is_array($child)) {
                $definition->addMethodCall('addChild', array($name, $child));
            } else {
                $definition->addMethodCall('addChild', array($child));
            }
        }
    }

    /**
     * @param array $configs
     *
     * @return array
     */
    private function validateMenuItemConfig(array $configs)
    {
        // Keep it static to prevent to many objects
        if (!self::$configTree) {
            $configTree = new TreeBuilder();
            $node = $configTree->root('item');

            $configuration = new Configuration($this->getAlias());
            $configuration->addItemConfig($node);

            self::$configTree = $configTree;
        }

        $processor = new Processor();

        return $processor->process(self::$configTree->buildTree(), array($configs));
    }

    /**
     * Resolves parameters.
     *
     * @param string $value
     *
     * @return Reference
     */
    private function resolveParameters($value)
    {
        if (is_array($value)) {
            $value = array_map(array($this, 'resolveParameters'), $value);
        } elseif (is_string($value) && 0 === strpos($value, '@')) {
            if ('@' === substr($value, 1, 1)) {
                return substr($value, 1);
            }

            return new Expression(substr($value, 1));
        }

        return $value;
    }
}
