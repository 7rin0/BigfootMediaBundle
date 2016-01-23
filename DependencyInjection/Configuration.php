<?php

namespace Bigfoot\Bundle\MediaBundle\DependencyInjection;

use Bigfoot\Bundle\MediaBundle\Form\Type\BigfootMediaType;
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
        $rootNode = $treeBuilder->root(BigfootMediaType::class);

        $rootNode
            ->children()
                ->scalarNode('provider')->defaultNull()->end()
                ->scalarNode('cache')->defaultValue(true)->end()
                ->integerNode('pagination_per_page')
                    ->treatNullLike(5)
                    ->defaultValue(5)
                    ->min(1)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
