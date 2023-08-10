<?php

namespace Fastbolt\EntityArchiverBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class EntityArchiverBundleExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        //inject entity-archiver.yaml settings into the command
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('Fastbolt\EntityArchiverBundle\Command\EntityArchiverCommand');
        $definition
            ->setArgument(0, new Reference('Fastbolt\EntityArchiverBundle\ArchiveManager'))
            ->setArgument(1, $config)
            ->addTag('console.command', ['command' => 'entity-archiver:run']);
    }
}
