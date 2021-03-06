<?php

namespace App\Controller;

use GeoSocio\EntityAttacher\EntityAttacherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * Creates the Controller.
     *
     * @param DenormalizerInterface $denormalizer
     * @param RegistryInterface $doctrine
     * @param EntityAttacherInterface $attacher
     * @param AuthorizationCheckerInterface $attacher
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        RegistryInterface $doctrine,
        EntityAttacherInterface $attacher,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->denormalizer = $denormalizer;
        $this->doctrine = $doctrine;
        $this->attacher = $attacher;
        $this->authorizationChecker = $authorizationChecker;
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
