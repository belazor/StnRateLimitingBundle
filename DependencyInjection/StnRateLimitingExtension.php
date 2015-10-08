<?php

namespace Stn\RateLimitingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class StnRateLimitingExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('stn_rate_limiting', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->initClient($container, $config);
    }

    /**
     * Initialize redis client
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function initClient(ContainerBuilder $container, Array $config)
    {
        $client = $container->getDefinition('stn_rate_limiting.cache.redis_client');

        if (true === isset($config['client']['dsn'])) {
            $dsn = (string) $config['client']['dsn'];

            $client->addArgument($dsn);
        }

        if (true == isset($config['client']['pass']) && '' !== $pass = (string) $config['client']['pass']) {
            $client->addMethodCall('auth', array( $pass ));
        }
    }
}
