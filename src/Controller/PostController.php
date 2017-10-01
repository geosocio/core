<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Place\Place;
use App\Entity\Post\Post;
use App\Entity\Post\Placement;
use App\Entity\User\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
 */
class PostController extends Controller
{

    /**
     * @Route("/post.{_format}")
     * @Method("GET")
     *
     * @param Request $request
     *
     * @return array
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
     * @Security("is_granted('view', post)", statusCode=404)
     *
     * @param Post $post
     *
     * @return Post
     */
    public function showAction(Post $post) : Post
    {
        return $post;
    }

    /**
     * @Route("/post")
     * @Method("POST")
     * @Security("is_granted('create', post)")
     *
     * @param Post $post
     *
     * @return Post
     *
     * @todo Creating a post without a place should defult to user's place.
     */
    public function createAction(Post $post) : Post
    {
        $em = $this->doctrine->getEntityManager();

        $repository = $this->doctrine->getRepository(Post::class);

        $post = $this->attacher->attach($post);

        $em->persist($post);
        $em->flush();

        return $post;
    }

    /**
     * @Route("/post/{post}.{_format}")
     * @Method("DELETE")
     * @Security("is_granted('delete', post)")
     *
     * @param Post $post
     *
     * @return string
     */
    public function removeAction(Post $post) : string
    {
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
     * @Security("is_granted('view', post)")
     *
     * @param Post $post
     * @param Request $request
     *
     * @return array
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

    /**
     * @Route("/post/{post}/place/{user}.{_format}")
     * @Method("POST")
     * @ParamConverter("post", converter="doctrine.orm", class="App\Entity\Post\Post")
     *
     * @todo add security voter for Placements to ensure that user does not
     *       place for some other user!
     *
     * @param Post $post
     * @param User $user
     */
    public function placeAction(Post $post, User $user) : array
    {
        if (!$user->getPlace()) {
            throw new BadRequestHttpException('User has no place');
        }

        if ($post->getUserPlacement($user)) {
            throw new BadRequestHttpException('User has already placed post');
        }

        $em = $this->doctrine->getEntityManager();

        $placement = new Placement([
            'post' => $post,
            'user' => $user,
            'place' => $user->getPlace()
        ]);
        $place->addPlacement($placement);

        $em->persist($placement);
        $em->flush();

        return $placement;
    }
}
