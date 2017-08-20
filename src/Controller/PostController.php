<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Place\Place;
use App\Entity\Post\Post;
use App\Entity\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Post actions.
 *
 * @Route(
 *    service="app.controller_post",
 *    defaults = {
 *       "version" = "1.0",
 *       "_format" = "json"
 *    }
 * )
 * @TODO Need a route to "repost"
 */
class PostController extends Controller
{

    /**
     * @Route("/post.{_format}")
     * @Method("GET")
     */
    public function indexAction(Request $request) : array
    {
        $repository = $this->doctrine->getRepository(Post::class);

        $place = null;
        if ($request->query->has('placeId')) {
            $placeId = (int) $request->query->get('placeId');

            $repository = $this->doctrine->getRepository(Place::class);
            $place = $repository->find($placeId);

            if (!$place) {
                throw new BadRequestHttpException('Place not found');
            }
        }

        $site = null;
        if ($request->query->has('siteId')) {
            $siteId = (string) $request->query->get('siteId');

            $repository = $this->doctrine->getRepository(Site::class);
            $site = $repository->find($siteId);

            if (!$site) {
                throw new BadRequestHttpException('Site not found');
            }
        }

        $repository = $this->doctrine->getRepository(Post::class);

        return $repository->findBySitePlace($site, $place, $this->getLimit($request), $this->getOffset($request));
    }

    /**
     * @Route("/post/{post}.{_format}")
     * @Method("GET")
     * @ParamConverter("post", converter="doctrine.orm", class="App\Entity\Post\Post")
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
     *
     * @TODO Creating a post without a place should defult to user's place.
     */
    public function createAction(User $authenticated, array $input) : Post
    {
        $post = $this->denormalizer->denormalize($input, Post::class);

        $em = $this->doctrine->getEntityManager();

        $repository = $this->doctrine->getRepository(Post::class);

        if (!$post->canCreate($authenticated)) {
            throw new AccessDeniedHttpException();
        }

        $post = $this->attacher->attach($post);

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

    /**
     * @Route("/post/{post}/replies.{_format}")
     * @Method("GET")
     * @ParamConverter("post", converter="doctrine.orm", class="App\Entity\Post\Post")
     */
    public function showRepliesAction(Post $post, Request $request) : array
    {
        $repository = $this->doctrine->getRepository(Post::class);

        return $repository->findByReply(
            $post,
            ['created' => 'DESC'],
            $this->getLimit($request),
            $this->getOffset($request)
        );
    }
}
