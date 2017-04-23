<?php

namespace GeoSocio\Core\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

use GeoSocio\Core\Entity\Site as SiteEntity;
use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\Verify\EmailVerify;

/**
 * GeoSocio\Core\Entity\User\Site
 *
 * @ORM\Table(name="users_site")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Site implements UserAwareInterface
{

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sites")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\GeoSocio\Core\Entity\Site")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="site_id")
     * @Groups({"me_read", "me_write"})
     */
    private $site;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     * @Groups({"me_read"})
     */
    private $created;

    /**
     * Create new Email.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $user = $data['user'] ?? null;
        $this->user = $this->getSingle($user, User::class);

        $site = $data['site'] ?? null;
        $this->site = $this->getSingle($site, SiteEntity::class);

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
    }


    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue() : self
    {
        $this->created = new \DateTime();

        return $this;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Email
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
     * Set site
     */
    public function setSite(SiteEntity $site) : self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     */
    public function getSite() :? SiteEntity
    {
        return $this->site;
    }

    /**
     * Set created
     *
     * @param \DateTimeInterface $created
     */
    public function setCreated(\DateTimeInterface $created) : self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() :? \DateTimeInterface
    {
        return $this->created;
    }

    /**
     * Convers the email object to a string.
     */
    public function __toString() : string
    {
        return $this->email;
    }
}
