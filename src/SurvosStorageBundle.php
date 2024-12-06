<?php

/** generated from /home/tac/g/survos/survos/vendor/survos/maker-bundle/templates/skeleton/bundle/src/Bundle.tpl.php */

namespace Survos\StorageBundle;

use Survos\StorageBundle\Command\StorageConfigCommand;
use Survos\StorageBundle\Command\StorageListCommand;
use Survos\StorageBundle\Command\StorageDownloadCommand;
use Survos\StorageBundle\Command\StorageUploadCommand;
use Survos\StorageBundle\Controller\StorageController;
use Survos\StorageBundle\Service\StorageService;
use Survos\StorageBundle\Twig\TwigExtension;
use Survos\SimpleDatatables\SurvosSimpleDatatablesBundle;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SurvosStorageBundle extends AbstractBundle
{

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // get all bundles https://symfony.com/doc/current/bundles/prepend_extension.html
        $bundles = $builder->getParameter('kernel.bundles');
        $hasSimpleDatatables = in_array(SurvosSimpleDatatablesBundle::class, array_values($bundles));

        $serviceId = 'survos_storage.storage_service';
        $container->services()->alias(StorageService::class, $serviceId);
        $builder->autowire($serviceId, StorageService::class)
            ->setArgument('$config', $config)
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setPublic(true);

        $builder->autowire(StorageController::class)
            ->setArgument('$simpleDatatablesInstalled', $hasSimpleDatatables)
            ->addTag('container.service_subscriber')
            ->addTag('controller.service_arguments')
        ;

//        foreach ([StorageConfigCommand::class, StorageListCommand::class, StorageUploadCommand::class, StorageDownloadCommand::class] as $commandName) {
//            $builder->autowire($commandName)
//                ->setAutoconfigured(true)
//                ->addTag('console.command')
//            ;
//        }
//
//        // twig classes, for storage_url ?
//        $builder
//            ->autowire('survos.storage_twig', TwigExtension::class)
//            ->setAutoconfigured(true)
//            ->addTag('twig.extension');
    }

    private function addZonesSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->arrayNode('zones')
            ->arrayPrototype()
            ->children()
                ->scalarNode('name')->end()
                ->scalarNode('id')->end()
                ->scalarNode('region')->end()
                ->scalarNode('readonly_password')->end()
                ->scalarNode('password')->end()
            ->end()
            ->end()
            ->end();

    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->scalarNode('api_key')->defaultNull()->end()
                ->scalarNode('storage_zone')->defaultValue(null)->end()
//                ->scalarNode('region')->defaultValue(null)->end()
//                ->scalarNode('readonly_password')->defaultValue(null)->end()
//                ->scalarNode('password')->defaultValue(null)->end()
            ->end();

        $this->addZonesSection($rootNode);
    }

}
