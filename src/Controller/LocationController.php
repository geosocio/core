<?php

namespace App\Controller;

use App\Client\Mapzen\SearchInterface;
use App\Entity\Location;
use GeoSocio\EntityAttacher\EntityAttacherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Location actions.
 *
 * @Route(
 *    service="app.controller_location",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 */
class LocationController extends Controller
{

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        RegistryInterface $doctrine,
        EntityAttacherInterface $attacher,
        AuthorizationCheckerInterface $authorizationChecker,
        SearchInterface $search
    ) {
        parent::__construct($denormalizer, $doctrine, $attacher, $authorizationChecker);

        $this->search = $search;
    }

    /**
     * @Route("/location/{id}.{_format}")
     * @Method("GET")
     *
     * @param Request $request
     */
    public function showAction(string $id) : Location
    {
        return $this->search->get($id)->wait();
    }

    /**
     * @Route("/location/search.{_format}")
     * @Method("GET")
     *
     * @param Request $request
     */
    public function searchAction(Request $request) : array
    {
        if (!$request->query->get('text')) {
            throw new BadRequestHttpException('Missing search text');
        }

        return $this->search->search($request->query->get('text'))->wait();
    }
}
