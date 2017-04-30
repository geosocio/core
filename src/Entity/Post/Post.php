<?php

namespace GeoSocio\Core\Entity\Post;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\Entity;
use GeoSocio\Core\Entity\CreatedTrait;
use GeoSocio\Core\Entity\TreeAwareInterface;
use GeoSocio\Core\Entity\SiteAwareInterface;
use GeoSocio\Core\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use GeoSocio\Core\Entity\User\UserAwareInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GeoSocio\Entity\Location
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="post")
 */
class Post extends Entity implements SiteAwareInterface, UserAwareInterface, TreeAwareInterface
{

    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="post_id", type="guid")
     * @ORM\Id
     * @Assert\Uuid
     * @Groups({"anonymous_read", "me_write"})
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
    private $ancestor;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Tree", mappedBy="ancestor")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="ancestor")
     */
    private $descendant;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20000, nullable=true)
     * @Groups({"anonymous_read", "me_write"})
     */
    private $text;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\GeoSocio\Core\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * @var Site
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\GeoSocio\Core\Entity\Site")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="site_id")
     */
    private $site;

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

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = new \DateTime();
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
     * Set Site
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
        $this->ancestor[] = $ancestor;

        return $this;
    }

    /**
     * Remove ancestor
     *
     * @param Tree $ancestor
     */
    public function removeAncestor(Tree $ancestor) : self
    {
        $this->ancestor->removeElement($ancestor);

        return $this;
    }

    /**
     * Get ancestor
     */
    public function getAncestor() : Collection
    {
        return $this->ancestor;
    }

    /**
     * Add descendant
     *
     * @param Tree $descendant
     */
    public function addDescendant(Tree $descendant) : self
    {
        $this->descendant[] = $descendant;

        return $this;
    }

    /**
     * Remove descendant
     *
     * @param Tree $descendant
     */
    public function removeDescendant(Tree $descendant) : self
    {
        $this->descendant->removeElement($descendant);
    }

    /**
     * Get descendant
     */
    public function getDescendant() : Collection
    {
        return $this->descendant;
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
}
