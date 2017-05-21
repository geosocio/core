<?php

namespace GeoSocio\Core\Entity\Post;

use Doctrine\ORM\Mapping as ORM;
use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\Entity;
use GeoSocio\Core\Entity\CreatedTrait;
use GeoSocio\Core\Entity\SiteAwareInterface;
use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\UserAwareInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="post_placement")
 */
class Placement extends Entity implements UserAwareInterface, SiteAwareInterface
{

    use CreatedTrait;

    /**
     * @var Post
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Post", cascade={"merge"})
     * @ORM\JoinColumn(name="post_id", referencedColumnName="post_id")
     */
    private $post;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="GeoSocio\Core\Entity\User\User", cascade={"merge"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="GeoSocio\Core\Entity\Place\Place", inversedBy="locations", cascade={"merge"})
     * @ORM\JoinColumn(name="place_id", referencedColumnName="place_id")
     */
    private $place;

    /**
     * Create new Tree.
     */
    public function __construct(array $data = [])
    {
        $post = $data['post'] ?? null;
        $this->post = $this->getSingle($post, Post::class);

        $user = $data['user'] ?? null;
        $this->user = $this->getSingle($user, User::class);

        $place = $data['place'] ?? null;
        $this->place = $this->getSingle($place, Place::class);

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
    }

    /**
     * Set post.
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
     */
    public function setUser(User $user) : self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser() : User
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
