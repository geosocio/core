<?php

namespace App\Entity\Post;

use GeoSocio\EntityAttacher\Annotation\Attach;
use GeoSocio\EntityUtils\CreatedTrait;
use GeoSocio\EntityUtils\ParameterBag;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\Site;
use App\Entity\Permission;
use App\Entity\SiteAwareInterface;
use App\Entity\TreeAwareInterface;
use App\Entity\Place\Place;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User\UserAwareInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

// @codingStandardsIgnoreStart
/**
 * Post
 *
 * @ORM\Entity(repositoryClass="App\Repository\Post\PostRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="post")
 * @Assert\Expression(
 *     "(this.getPermission() and this.getPermission().getId() != 'place') or (this.getPermission() and this.getPermission().getId() == 'place' and this.getPermissionPlace())",
 *     message="Post with permission of 'place' must include a 'permissionPlace'"
 * )
 * @TODO Replies cannot have a placeId.
 * @TODO Create a property for the primaryPlacement.
 * @TODO Replies and forwards should have the same site id as their parent.
 */
// @codingStandardsIgnoreEnd
class Post implements UserAwareInterface, SiteAwareInterface, TreeAwareInterface
{

    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="post_id", type="guid")
     * @ORM\Id
     * @Assert\Uuid
     */
    private $id;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(name="reply", referencedColumnName="post_id")
     * @Attach()
     */
    private $reply;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post", cascade={"merge"})
     * @ORM\JoinColumn(name="forward", referencedColumnName="post_id")
     * @Attach()
     */
    private $forward;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Tree", mappedBy="descendant")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="descendant")
     */
    private $ancestors;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Tree", mappedBy="ancestor")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="ancestor")
     */
    private $descendants;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20000, nullable=true)
     * @Assert\NotBlank()
     */
    private $text;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * @Assert\NotNull()
     * @Attach()
     */
    private $user;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="posts")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="site_id")
     * @Attach()
     */
    private $site;

    /**
     * @var Permission
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Permission")
     * @ORM\JoinColumn(name="permission_id", referencedColumnName="permission_id")
     * @Assert\NotNull()
     * @Attach()
     */
    private $permission;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\Place\Place")
     * @ORM\JoinColumn(name="permission_place_id", referencedColumnName="place_id")
     * @Attach()
     */
    private $permissionPlace;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deleted;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Placement", mappedBy="post", cascade={"persist"})
     * @Attach()
     */
    private $placements;

    /**
     * Create new Location.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->id = $params->getUuid('id', strtolower(uuid_create(UUID_TYPE_DEFAULT)));
        $this->text = $params->getString('text');
        $this->user = $params->getInstnace('user', User::class);
        $this->site = $params->getInstnace('site', Site::class);
        $this->permission = $params->getInstnace('permission', Permission::class);
        $this->permissionPlace = $params->getInstnace('permissionPlace', Place::class);
        $this->created = $params->getInstnace('created', \DateTimeInterface::class);
        $this->deleted = $params->getInstnace('deleted', \DateTimeInterface::class);
        $this->placements = $params->getCollection('placements', Placement::class, new ArrayCollection());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatePermissionPlace()
    {
        if (!$this->permission || $this->permission->getId() !== 'place') {
            $this->permissionPlace = null;
        }
    }

    /**
     * Get id
     *
     * @Groups({"anonymous"})
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId(string $id) : self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get Text
     *
     * @Groups({"anonymous"})
     */
    public function getText() :? string
    {
        return $this->text;
    }

    /**
     * Set Text
     *
     * @param string $text
     *
     * @Groups({"standard"})
     */
    public function setText(string $text) : self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Set user
     *
     * @param User $user
     */
    public function setUser(User $user) : self
    {
        $this->user = $user;

        // Update the primary placement if it exists.
        if ($placement = $this->getPrimaryPlacement()) {
            $placement->setUser($user);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser() :? User
    {
        return $this->user;
    }

    /**
     * Get User id.
     *
     * @Groups({"anonymous"})
     */
    public function getUserId() :? string
    {
        if (!$this->user) {
            return null;
        }

        return $this->user->getId();
    }

    /**
     * Set User id.
     *
     * @param string $id
     *
     * @Groups({"standard"})
     */
    public function setUserId(string $id) : self
    {
        return $this->setUser(new User([
            'id' => $id,
        ]));
    }

    /**
     * Set site
     *
     * @param string $id
     *
     * @Groups({"standard"})
     */
    public function setSiteId(string $id) : self
    {
        $this->site = new Site([
            'id' => $id,
        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @Groups({"anonymous"})
     */
    public function getSiteId() :? string
    {
        if (!$this->site) {
            return null;
        }

        return $this->site->getId();
    }

    /**
     * Set site
     *
     * @param Site $site
     */
    public function setSite(Site $site) : self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSite() :? Site
    {
        return $this->site;
    }

    /**
     * Add ancestor
     *
     * @param Tree $ancestor
     */
    public function addAncestor(Tree $ancestor) : self
    {
        $this->ancestors[] = $ancestor;

        return $this;
    }

    /**
     * Remove ancestor
     *
     * @param Tree $ancestor
     */
    public function removeAncestor(Tree $ancestor) : self
    {
        $this->ancestors->removeElement($ancestor);

        return $this;
    }

    /**
     * Get ancestor
     */
    public function getAncestors() : Collection
    {
        return $this->ancestors;
    }

    /**
     * Add descendant
     *
     * @param Tree $descendant
     */
    public function addDescendant(Tree $descendant) : self
    {
        $this->descendants[] = $descendant;

        return $this;
    }

    /**
     * Remove descendant
     *
     * @param Tree $descendant
     */
    public function removeDescendant(Tree $descendant) : self
    {
        $this->descendants->removeElement($descendant);
    }

    /**
     * Get descendant
     */
    public function getDescendants() : Collection
    {
        return $this->descendants;
    }

    /**
     * Set reply id.
     *
     * @param string $id
     *
     * @Groups({"standard"})
     */
    public function setReplyId(string $id) : self
    {
        $this->reply = new Post([
            'id' => $id,
        ]);

        return $this;
    }

    /**
     * Get the reply id.
     *
     * @Groups({"anonymous"})
     */
    public function getReplyId() :? string
    {
        if (!$this->reply) {
            return null;
        }

        return $this->reply->getId();
    }

    /**
     * Set reply
     *
     * @param Post $reply
     */
    public function setReply(Post $reply) : self
    {
        $this->reply = $reply;

        return $this;
    }

    /**
     * Get reply
     */
    public function getReply() :? Post
    {
        return $this->reply;
    }

    /**
     * Set forward id.
     *
     * @param string $id
     *
     * @Groups({"standard"})
     */
    public function setForwardId(string $id) : self
    {
        $this->forward = new Post([
            'id' => $id,
        ]);

        return $this;
    }

    /**
     * Get the forward id.
     *
     * @Groups({"anonymous"})
     */
    public function getForwardId() :? string
    {
        if (!$this->forward) {
            return null;
        }

        return $this->forward->getId();
    }

    /**
     * Set forward
     *
     * @param Post $forward
     */
    public function setForward(Post $forward) : self
    {
        $this->forward = $forward;

        return $this;
    }

    /**
     * Set forward
     */
    public function getForward() :? Post
    {
        return $this->forward;
    }

    /**
     * Set permission id.
     *
     * @param string $id
     *
     * @Groups({"standard"})
     */
    public function setPermissionId(string $id) : self
    {
        $this->permission = new Permission([
            'id' => $id,
        ]);

        return $this;
    }

    /**
     * Get the permission id.
     *
     * @Groups({"anonymous"})
     */
    public function getPermissionId() :? string
    {
        if (!$this->permission) {
            return null;
        }

        return $this->permission->getId();
    }

    /**
     * Set Permission.
     *
     * @param Permission $permission
     */
    public function setPermission(Permission $permission) : self
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get Permission.
     */
    public function getPermission() :? Permission
    {
        return $this->permission;
    }

    /**
     * Set permission place id.
     *
     * @param string $id
     *
     * @Groups({"standard"})
     */
    public function setPermissionPlaceId(string $id) : self
    {
        $this->permissionPlace = new Place([
            'id' => $id,
        ]);

        return $this;
    }

    /**
     * Get the permission place id.
     *
     * @Groups({"anonymous"})
     */
    public function getPermissionPlaceId() :? string
    {
        if (!$this->permissionPlace) {
            return null;
        }

        return $this->permissionPlace->getId();
    }

    /**
     * Set Permission Place.
     *
     * @param Place $permissionPlace
     */
    public function setPermissionPlace(Place $permissionPlace) : self
    {
        $this->permissionPlace = $permissionPlace;

        return $this;
    }

    /**
     * Get Permission.
     */
    public function getPermissionPlace() :? Place
    {
        return $this->permissionPlace;
    }

    /**
     * Get Placements.
     */
    public function getPlacements() : Collection
    {
        return $this->placements;
    }

    /**
     * Add Placement.
     *
     * @param Placement $placement
     */
    public function addPlacement(Placement $placement) : self
    {
        $this->placements->add($placement);

        return $this;
    }

    /**
     * Remove Placement.
     *
     * @param Placement $placement
     */
    public function removePlacement(Placement $placement) : self
    {
        $this->placements->remove($placement);

        return $this;
    }

    /**
     * Get Placements.
     *
     * @param Collection $placements
     */
    public function setPlacements(Collection $placements) : self
    {
        $this->placements = $placements;

        return $this;
    }

    /**
     * Get Primary Placement.
     *
     * @param User $user
     */
    public function getUserPlacement(User $user) :? Placement
    {

        if (!$this->placements->count()) {
            return null;
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("user", $user));

        $placement = $this->placements->matching($criteria)->first();

        if (!$placement) {
            return null;
        }

        return $placement;
    }

    /**
     * Get Primary Placement.
     */
    public function getPrimaryPlacement() :? Placement
    {

        if (!$this->placements->count()) {
            return null;
        }

        if ($this->placements->count() == 1) {
            return $this->placements->first();
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("user", $this->user));

        $placement = $this->placements->matching($criteria)->first();

        if (!$placement) {
            return null;
        }

        return $placement;
    }

    /**
     * Set place
     *
     * @param int $id
     *
     * @Groups({"standard"})
     */
    public function setPlaceId(int $id) : self
    {
        $placement = $this->getPrimaryPlacement();

        if (!$placement) {
            $this->placements->add(new Placement([
                'post' => $this,
                'user' => $this->user,
                'place' => new Place([
                    'id' => $id,
                ]),
            ]));

            return $this;
        }

        $placement->setPlace(new Place([
            'id' => $id,
        ]));

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @Groups({"me", "neighbor"})
     */
    public function getPlaceId() :? int
    {
        $placement = $this->getPrimaryPlacement();

        if (!$placement) {
            return null;
        }

        $place = $placement->getPlace();

        if (!$place) {
            return null;
        }

        return $place->getId();
    }

    /**
     * Delte the post.
     */
    public function delete() : self
    {
        $this->deleted = new \DateTime();

        return $this;
    }

    /**
     * Un delete the post.
     */
    public function undelete() : self
    {
        $this->deleted = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent() :? Post
    {
        return $this->reply;
    }

    /**
     * {@inheritdoc}
     */
    public function getTreeClass() : string
    {
        return Tree::class;
    }

    /**
     * Get Enabled.
     *
     * @Groups({"anonymous"})
     */
    public function isDeleted() : bool
    {
        return (bool) $this->deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlaceholder() : Post
    {
        return new Post([
            "id" => $this->getId(),
        ]);
    }

    /**
     * Get created
     *
     * @Groups({"anonymous"})
     */
    public function getCreated() :? \DateTimeInterface
    {
        return $this->created;
    }

    /**
     * Clone magic method.
     */
    public function __clone()
    {
        if ($placement = $this->getPrimaryPlacement()) {
            $placement->setPost($this);
        }
    }
}
