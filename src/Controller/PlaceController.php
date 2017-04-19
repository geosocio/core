<?php

namespace GeoSocio\Core\Controller;

use GeoSocio\Core\Entity\Place\Place;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Place actions.
 *
 * @Route(
 *    service="geosocio.controller_place",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 */
class PlaceController extends Controller
{

    /**
     * @Route("/place.{_format}")
     * @Method("GET")
     *
     * @param Request $request
     */
    public function indexAction(Request $request) : Place
    {
        if (!$request->query->has('slug')) {
            throw new BadRequestHttpException('Slug is a required paramater');
        }

        $repository = $this->doctrine->getRepository(Place::class);

        $place = $repository->findOneBySlug($request->query->get('slug'));

        if (!$place) {
            throw new NotFoundHttpException('Place Not Found');
        }

        return $place;
    }

    /**
     * @Route("/place/{place}.{_format}")
     * @Method("GET")
     *
     * @param Place $place
     * @param Request $request
     */
    public function showAction(Place $place) : Place
    {
        return $place;
    }
}
