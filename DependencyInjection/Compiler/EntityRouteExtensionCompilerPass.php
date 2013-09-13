<?php
namespace Egzakt\SystemBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class EntityRouteExtensionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('egzakt_system.entity_route')) {
            return;
        }

        $definition = $container->getDefinition('egzakt_system.entity_route');
        $taggedServices = $container->findTaggedServiceIds('egzakt_system.entity_route');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('registerMapping', array(new Reference($id)));
        }

    }
}