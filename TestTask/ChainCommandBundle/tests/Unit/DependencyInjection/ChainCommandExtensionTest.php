<?php

namespace TestTask\ChainCommandBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TestTask\ChainCommandBundle\DependencyInjection\ChainCommandExtension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class ChainCommandExtensionTest extends TestCase
{
    /**
     * @var ChainCommandExtension
     */
    private ChainCommandExtension $extension;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Method setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->container = $this->createContainer();
        $this->extension = new ChainCommandExtension();
    }

    /**
     * Method testLoad
     *
     * @covers ChainCommandExtension::load
     *
     * @return void
     */
    public function testLoad(): void
    {
        $this->extension->load([], $this->container);

        $this->assertTrue(
            $this->container->has('chain_command.manager'),
            'ChainCommandExtension was Not loaded properly'
        );
    }

    /**
     * Creates container
     *
     * @return ContainerInterface
     * @throws \Exception
     */
    private function createContainer(): ContainerInterface
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new ChainCommandExtension());

        // Load the configuration
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../../config'));
        $loader->load('services.yaml');

        return $container;
    }
}