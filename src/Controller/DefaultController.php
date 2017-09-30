<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="app.controller_default")
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
     * @return array
     */
    public function indexAction() : array
    {
        return ['hello' => 'world!'];
    }
}
