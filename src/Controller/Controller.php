<?php

namespace GeoSocio\Core\Controller;

use GeoSocio\Core\Utils\EntityAttacherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @var EntityAttacherInterface
     */
    protected $attacher;

    /**
     * Creates the Controller.
     *
     * @param DenormalizerInterface $denormalizer
     * @param RegistryInterface $doctrine
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        RegistryInterface $doctrine,
        EntityAttacherInterface $attacher
    ) {
        $this->denormalizer = $denormalizer;
        $this->doctrine = $doctrine;
        $this->attacher = $attacher;
    }

    /**
     * Gets the offset from the Request.
     */
    public function getOffset(Request $request) : int
    {
        $limit = $this->getLimit($request);
        $page = (int) $request->query->get('page', 1);
        $offset = ($page * $limit) - $limit;

        // Offset cannot be negative.
        if ($offset < 0) {
            $offset = 0;
        }

        return $offset;
    }

    /**
     * Gets the limit from the Request.
     */
    public function getLimit(Request $request) : int
    {
        return $request->query->get('limit', 5);
    }
}
