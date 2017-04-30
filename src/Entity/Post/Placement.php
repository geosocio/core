<?php

namespace GeoSocio\Core\Entity\Post;

use Doctrine\ORM\Mapping as ORM;
use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\Entity;
use GeoSocio\Core\Entity\CreatedTrait;
use GeoSocio\Core\Entity\SiteAwareInterface;
use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\Membership;
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
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="post_id")
     */
    private $post;

    /**
     * @var Membership
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\GeoSocio\Core\Entity\Membership")
     * @ORM\JoinColumn(name="membership_id", referencedColumnName="membership_id")
     */
    private $membership;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="GeoSocio\Core\Entity\Place\Place", inversedBy="locations")
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

        $membership = $data['membership'] ?? null;
        $this->user = $this->getSingle($user, Membership::class);

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
     * Set membership.
     */
    public function setMembership(Membership $membership) : self
    {
        $this->membership = $membership;

        return $this;
    }

    /**
     * Get membership.
     */
    public function getMembership() : Membership
    {
        return $this->membership;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser() :? User
    {
        return $this->membership ? $this->membership->getUser() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSite() :? Site
    {
        return $this->membership ? $this->membership->getSite() : null;
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
