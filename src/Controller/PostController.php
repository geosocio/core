<?php

namespace GeoSocio\Core\Controller;

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

        $repository = $this->doctrine->getRepository(Post::class);

        if (!$post->canCreate($authenticated)) {
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
