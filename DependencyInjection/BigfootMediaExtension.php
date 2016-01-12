<?php

namespace Bigfoot\Bundle\MediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BigfootMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('bigfoot_media.provider', $config['provider']);
        $container->setParameter('bigfoot_media.cache', $config['cache']);
        $container->setParameter('bigfoot_media.pagination_per_page', $config['pagination_per_page']);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $loaded  = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $loaded);

        if (isset($bundles['TwigBundle'])) {
            $container->prependExtensionConfig(
                'twig',
                array(
                    'globals' => array(
                        'bigfoot_media_cache' => isset($config['cache']) ? $config['cache'] : true
                    )
                )
            );
        }
    }
}
