<?php

namespace GeoSocio\Core;

use GeoSocio\Core\DependencyInjection\Compiler\VerificationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Core Bundle.
 */
class Core extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new VerificationPass());
    }
}
