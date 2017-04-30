<?php

namespace GeoSocio\Core\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use GeoSocio\Core\Entity\CreatedTrait;
use Symfony\Component\Serializer\Annotation\Groups;

use GeoSocio\Core\Entity\Entity;
use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\SiteAwareInterface;

/**
 * GeoSocio\Core\Entity\User\Membership
 *
 * @ORM\Table(name="users_membership")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Membership extends Entity implements UserAwareInterface, SiteAwareInterface
{

    use CreatedTrait;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sites")
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
     * Create new Email.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $user = $data['user'] ?? null;
        $this->user = $this->getSingle($user, User::class);

        $site = $data['site'] ?? null;
        $this->site = $this->getSingle($site, Site::class);

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
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
    public function setSite(Site $site) : self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     */
    public function getSite() :? Site
    {
        return $this->site;
    }

    /**
     * Get the id (site id).
     *
     * @Groups({"me_read", "standard_read"})
     */
    public function getId() :? string
    {
        if (!$this->site) {
            return null;
        }

        return $this->site->getId();
    }
}
