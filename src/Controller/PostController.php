<?php

namespace GeoSocio\Core\Controller;

use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\Permission;
use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Entity\Post\Post;
use GeoSocio\Core\Entity\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Post actions.
 *
 * @Route(
 *    service="geosocio.controller_post",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 */
class PostController extends Controller
{

    /**
     * @Route("/post/{post}.{_format}")
     * @Method("GET")
     * @ParamConverter("post", converter="doctrine.orm", class="GeoSocio\Core\Entity\Post\Post")
     */
    public function showAction(Post $post, User $authenticated = null) : Post
    {
        if ($post->isDeleted()) {
            throw new NotFoundHttpException();
        }

        return $post;
    }

    /**
     * @Route("/post")
     * @Method("POST")
     * @Security("has_role('standard')")
     */
    public function createAction(User $authenticated, array $input) : Post
    {
        $post = $this->denormalizer->denormalize($input, Post::class, null, [
            'user' => $authenticated,
        ]);

        $em = $this->doctrine->getEntityManager();

        $post->setUser($em->find(User::class, $post->getUser()->getId()));

        $post->setPermission($em->find(Permission::class, $post->getPermission()->getId()));

        if ($post->getPermissionPlace()) {
            $post->setPermissionPlace($em->find(Place::class, $post->getPermissionPlace()->getId()));
        }

        // If the user is not a member of the site, set the site to null.
        if (!$post->getUser()->isMember($post->getSite())) {
            $post->setSite(null);
        }

        if ($post->getSite()) {
            $post->setSite($em->find(Site::class, $post->getSite()->getId()));
        }

        if (!$this->canCreate($post)) {
            throw new AccessDeniedHttpException();
        }

        $em->persist($post);
        $em->flush();

        return $this->showAction($post);
    }

    /**
     * @Route("/post/{post}.{_format}")
     * @Method("DELETE")
     * @Security("has_role('standard')")
     */
    public function removeAction(User $authenticated, Post $post) : string
    {
        if (!$post->canDelete($user)) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->doctrine->getEntityManager();

        $post->delete();

        $em->persist($post);
        $em->flush();

        return '';
    }
}
