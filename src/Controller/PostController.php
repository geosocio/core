<?php

namespace GeoSocio\Core\Controller;

use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\Permission;
use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Entity\Post\Post;
use GeoSocio\Core\Entity\User\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
     * @Route("/post.{_format}")
     * @Method("GET")
     *
     * @param Request $request
     */
    public function indexAction(Request $request) : Post
    {
        // @TODO return posts.
    }

    /**
     * @Route("/post/{post}.{_format}")
     * @Method("GET")
     * @ParamConverter("post", converter="doctrine.orm", class="GeoSocio\Core\Entity\Post\Post")
     */
    public function showAction(Post $post) : Post
    {
        return $post;
    }

    /**
     * @Route("/post")
     * @Method("POST")
     */
    public function createAction(User $authenticated, array $input) : Post
    {
        $post = $this->denormalizer->denormalize($input, Post::class, null, [
            'user' => $authenticated,
        ]);

        $em = $this->doctrine->getEntityManager();

        $post->setUser($em->find(User::class, $post->getUser()->getId()));

        // This should have already happened
        if (!$authenticated->isEqualTo($post->getUser())) {
            throw new AccessDeniedException("You may only create posts for yourself.");
        }

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

        $em->persist($post);
        $em->flush();

        return $this->showAction($post);
    }
}
