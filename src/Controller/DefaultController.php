<?php

namespace GeoSocio\Core\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="geosocio.controller_default")
 */
class DefaultController extends Controller
{

    /**
     * @Route("/",
     *  defaults= {
     *    "_format" = "json"
     *  }
     *)
     *
     * @param Request $request
     */
    public function indexAction() : array
    {
        return ['hello' => 'world!'];
    }
}