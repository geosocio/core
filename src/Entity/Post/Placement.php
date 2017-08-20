<?php

namespace App\Entity\Post;

use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityAttacher\Annotation\Attach;
use App\Entity\Site;
use App\Entity\SiteAwareInterface;
use App\Entity\Place\Place;
use App\Entity\User\User;
use App\Entity\User\UserAwareInterface;
use GeoSocio\EntityUtils\CreatedTrait;
use GeoSocio\EntityUtils\ParameterBag;

/**
 * @ORM\Entity
 * @ORM\Table(name="post_placement")
 */
class Placement implements UserAwareInterface, SiteAwareInterface
{

    use CreatedTrait;

    /**
     * @var Post
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="placements")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="post_id")
     */
    private $post;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * @Attach()
     */
    private $user;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Place\Place")
     * @ORM\JoinColumn(name="place_id", referencedColumnName="place_id")
     * @Attach()
     */
    private $place;

    /**
     * Create new Tree.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->post = $params->getInstance('post', Post::class);
        $this->user = $params->getInstance('user', User::class);
        $this->place = $params->getInstance('place', Place::class);
        $this->created = $params->getInstance('created', \DateTimeInterface::class, new \DateTime());
    }

    /**
     * Set post.
     *
     * @param Post $post
     */
    public function setPost(Post $post) : self
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post.
     */
    public function getPost() : Post
    {
        return $this->post;
    }

    /**
     * Set user.
     *
     * @param User $user
     */
    public function setUser(User $user) : self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser() :? User
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function getSite() :? Site
    {
        return $this->post ? $this->post->getSite() : null;
    }

    /**
     * Set place.
     *
     * @param Place $place
     */
    public function setPlace(Place $place) : self
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place.
     */
    public function getPlace() :? Place
    {
        return $this->place;
    }
}
