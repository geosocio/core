<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Adds tagged verifiers to the verification manager.
 */
class VerificationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container) : void
    {
        // Always first check if the primary service is defined.
        if (!$container->has('app.verification_manager')) {
            return;
        }

        $definition = $container->getDefinition('app.verification_manager');
        $taggedServices = $container->findTaggedServiceIds('app.verification');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addVerification', array(
                    new Reference($id),
                    $attributes['type']
                ));
            }
        }
    }
}
