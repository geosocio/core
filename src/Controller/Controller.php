<?php

namespace GeoSocio\Core\Controller;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * An abstract controller to extend.
 */
abstract class Controller
{

    /**
     * @var DenormalizerInterface
     */
    protected $denormalizer;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * Creates the Controller.
     *
     * @param DenormalizerInterface $denormalizer
     * @param RegistryInterface $doctrine
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        RegistryInterface $doctrine
    ) {
        $this->denormalizer = $denormalizer;
        $this->doctrine = $doctrine;
    }
}
