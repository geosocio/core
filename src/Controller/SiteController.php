<?php

namespace App\Controller;

use App\Entity\Site;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Site actions.
 *
 * @Route(
 *    service="app.controller_site",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 */
class SiteController extends Controller
{

    /**
     * @Route("/site.{_format}")
     * @Method("GET")
     *
     * @Groups("anonymous")
     *
     * @param Request $request
     */
    public function indexAction(Request $request) : Site
    {
        $repository = $this->doctrine->getRepository(Site::class);

        if ($request->query->has('key')) {
            $site = $repository->findOneByKey($request->query->get('key'));

            if (!$site) {
                throw new NotFoundHttpException('Site Not Found');
            }

            return $site;
        }

        $site = $repository->findOneByDomain($request->getHost());

        if (!$site) {
            throw new NotFoundHttpException('Site Not Found');
        }

        return $site;
    }

    /**
     * @Route("/site/{site}.{_format}")
     * @Method("GET")
     * @ParamConverter("site", converter="doctrine.orm", class="App\Entity\Site")
     * @Groups("anonymous")
     */
    public function showAction(Site $site) : Site
    {
        return $site;
    }
}
