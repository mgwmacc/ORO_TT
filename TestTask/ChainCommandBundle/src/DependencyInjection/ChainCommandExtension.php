<?php

namespace TestTask\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class ChainCommandExtension
 *
 * Loads Bundle's own config
 */
class ChainCommandExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $loader->load($this->getConfigFileName());
    }

    /**
     * Loads specific Config for test purposes.
     *
     * @return string
     */
    private function getConfigFileName(): string
    {
        return (('test' == strtolower($_ENV['APP_ENV'])) ? 'services_test' : 'services') . '.yaml';
    }
}