<?php

namespace GeoSocio\Core\Entity\Post;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use GeoSocio\Core\Entity\AccessAwareInterface;
use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\Entity;
use GeoSocio\Core\Entity\Permission;
use GeoSocio\Core\Entity\CreatedTrait;
use GeoSocio\Core\Entity\SiteAwareInterface;
use GeoSocio\Core\Entity\TreeAwareInterface;
use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use GeoSocio\Core\Entity\User\UserAwareInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

// @codingStandardsIgnoreStart
/**
 * GeoSocio\Entity\Location
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="post")
 * @Assert\Expression(
 *     "(this.getPermission() and this.getPermission().getId() != 'place') or (this.getPermission() and this.getPermission().getId() == 'place' and this.getPermissionPlace())",
 *     message="Post with permission of 'place' must include a 'permissionPlace'"
 * )
 */
// @codingStandardsIgnoreEnd
class Post extends Entity implements AccessAwareInterface, UserAwareInterface, SiteAwareInterface, TreeAwareInterface
{

    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="post_id", type="guid")
     * @ORM\Id
     * @Assert\Uuid
     * @Groups({"anonymous_read"})
     */
    private $id;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(name="reply", referencedColumnName="post_id")
     */
    private $reply;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(name="forward", referencedColumnName="post_id")
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
     * @Groups({"anonymous_read", "me_write"})
     * @Assert\NotBlank()
     * @Groups({"anonymous_read", "me_write"})
     */
    private $text;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="GeoSocio\Core\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * @Assert\NotNull()
     */
    private $user;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="GeoSocio\Core\Entity\Site")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="site_id")
     */
    private $site;

    /**
     * @var Permission
     *
     * @ORM\ManyToOne(targetEntity="\GeoSocio\Core\Entity\Permission")
     * @ORM\JoinColumn(name="permission_id", referencedColumnName="permission_id")
     * @Assert\NotNull()
     */
    private $permission;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="\GeoSocio\Core\Entity\Place\Place")
     * @ORM\JoinColumn(name="permission_place_id", referencedColumnName="place_id")
     */
    private $permissionPlace;

    /**
     * Create new Location.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $id = $data['id'] ?? null;
        $this->id = is_string($id) && uuid_is_valid($id) ? strtolower($id) : strtolower(uuid_create(UUID_TYPE_DEFAULT));

        $text = $data['text'] ?? null;
        $this->text = is_string($text) ? $text : null;

        $user = $data['user'] ?? null;
        $this->user = $this->getSingle($user, User::class);

        $site = $data['site'] ?? null;
        $this->site = $this->getSingle($site, Site::class);

        $permission = $data['permission'] ?? null;
        $this->permission = $this->getSingle($permission, Permission::class);

        $permissionPlace = $data['permissionPlace'] ?? null;
        $this->permissionPlace = $this->getSingle($permissionPlace, Place::class);

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
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
     */
    public function getText() :? string
    {
        return $this->text;
    }

    /**
     * Set Text
     */
    public function setText(string $text) : self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Set user
     */
    public function setUser(User $user) : self
    {
        $this->user = $user;

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
     * @Groups({"anonymous_read"})
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
     * @Groups({"me_write"})
     */
    public function setUserId(string $id) : self
    {
        if (!$this->user) {
            $this->user = new User([
                'id' => $id,
            ]);

            return $this;
        }

        $this->user->setId($id);

        return $this;
    }

    /**
     * Set site
     *
     * @Groups({"me_write"})
     */
    public function setSiteId(string $id) : self
    {
        if (!$this->site) {
            $this->site = new Site([
                'id' => $id,
            ]);

            return $this;
        }

        $this->site->setId($id);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @Groups({"anonymous_read"})
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
     * @Groups({"me_write"})
     */
    public function setReplyId(string $id) : self
    {
        if (!$this->reply) {
            $this->reply = new Post([
                'id' => $id,
            ]);

            return $this;
        }

        $this->reply->setId($id);

        return $this;
    }

    /**
     * Get the reply id.
     *
     * @Groups({"anonymous_read"})
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
     * @Groups({"me_write"})
     */
    public function setForwardId(string $id) : self
    {
        if (!$this->forward) {
            $this->forward = new Post([
                'id' => $id,
            ]);

            return $this;
        }

        $this->forward->setId($id);

        return $this;
    }

    /**
     * Get the forward id.
     *
     * @Groups({"anonymous_read"})
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
     * @Groups({"me_write"})
     */
    public function setPermissionId(string $id) : self
    {
        if (!$this->permission) {
            $this->permission = new Permission([
                'id' => $id,
            ]);

            return $this;
        }

        $this->permission->setId($id);

        return $this;
    }

    /**
     * Get the permission id.
     *
     * @Groups({"anonymous_read"})
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
     * @Groups({"me_write"})
     */
    public function setPermissionPlaceId(string $id) : self
    {
        if (!$this->permissionPlace) {
            $this->permissionPlace = new Place([
                'id' => $id,
            ]);

            return $this;
        }

        $this->permissionPlace->setId($id);

        return $this;
    }

    /**
     * Get the permission place id.
     *
     * @Groups({"anonymous_read"})
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
     * {@inheritdoc}
     */
    public function canView(User $user = null) : bool
    {
        if (!$this->permission) {
            return false;
        }

        if ($this->permission->getId() === 'public') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($this->permission->getId() === 'me' && !$this->user->isEqualTo($user)) {
            return false;
        }

        if ($this->permission->getId() === 'site' && !$user->isMember($this->site)) {
            return false;
        }

        if ($this->permission->getId() === 'place') {
            if (!$this->permissionPlace) {
                return false;
            }
        }


        // @TODO need a method to get the user's places.

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreate(User $user = null) : bool
    {
        if (!$this->user || !$user) {
            return false;
        }

        if (!$this->user->isEqualTo($user)) {
            return false;
        }

        if ($this->site && $user->isMember($site)) {
            return false;
        }

        return $this->canView($user);
    }

    /**
     * {@inheritdoc}
     */
    public function canEdit(User $user = null) : bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canDelete(User $user = null) : bool
    {
        if (!$this->user || !$user) {
            return false;
        }

        return $this->user->isEqualTo($user);
    }
}
