<?php

namespace App\Entity\User;

use App\Entity\Site;
use App\Entity\SiteAwareInterface;
use App\Entity\User\User;
use App\Entity\User\UserAwareInterface;
use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityUtils\ParameterBag;
use GeoSocio\EntityUtils\CreatedTrait;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * App\Entity\User\Membership
 *
 * @ORM\Table(name="users_membership")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Membership implements UserAwareInterface, SiteAwareInterface
{

    use CreatedTrait;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Entity\User\User", inversedBy="sites")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * @var Site
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\App\Entity\Site")
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
        $params = new ParameterBag($data);
        $this->user = $params->getInstance('user', User::class);
        $this->site = $params->getInstance('site', Site::class);
        $this->created = $params->getInstance('created', \DateTimeInterface::class);
    }

    /**
     * Get Id.
     * @Groups({"read_anonymous"})
     */
    public function getId()
    {
        if (!$this->site) {
            return null;
        }

        return $this->site->getId();
    }

    /**
     * Set user
     *
     * @param User $user
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
     *
     * @param Site $site
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
}
