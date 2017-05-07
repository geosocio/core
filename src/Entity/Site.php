<?php

namespace GeoSocio\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GeoSocio\Entity\Location
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="site")
 */
class Site extends Entity implements SiteAwareInterface
{

    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="site_id", type="guid")
     * @ORM\Id
     * @Assert\Uuid
     * @Groups({"anonymous_read", "me_write"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, unique=true, nullable=true)
     * @Groups({"anonymous_read"})
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"anonymous_read"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({"anonymous_read"})
     */
    private $domain;

    /**
     * Create new Location.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $id = $data['id'] ?? null;
        $this->id = is_string($id) && uuid_is_valid($id) ? strtolower($id) : strtolower(uuid_create(UUID_TYPE_DEFAULT));

        $key = $data['key'] ?? null;
        $this->key = is_string($key) ? $key : null;

        $name = $data['name'] ?? null;
        $this->name = is_string($name) ? $name : null;

        $domain = $data['domain'] ?? null;
        $this->domain = is_string($domain) ? $domain : null;

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
     * Set id
     */
    public function setId(string $id) : self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Get Key
     */
    public function getKey() :? string
    {
        return $this->key;
    }

    /**
     * Set Key
     *
     * @param string $key
     */
    public function setKey(string $key) : self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get Name
     */
    public function getName() :? string
    {
        return $this->name;
    }

    /**
     * Set Name
     *
     * @param string $name
     */
    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Domain
     */
    public function getDomain() :? string
    {
        return $this->domain;
    }

    /**
     * Set Domain
     *
     * @param string $domain
     */
    public function setDomain(string $domain) : self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get the site.
     */
    public function getSite() : Site
    {
        return $this;
    }
}
