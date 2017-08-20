<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityUtils\ParameterBag;
use GeoSocio\EntityUtils\CreatedTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GeoSocio\Entity\Location
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="site")
 */
class Site implements SiteAwareInterface
{

    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="site_id", type="guid")
     * @ORM\Id
     * @Assert\Uuid
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, unique=true, nullable=true)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $domain;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Post\Post", cascade={"merge"}, mappedBy="site")
     */
    private $posts;

    /**
     * Create new Location.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->id = $params->getUuid('id', strtolower(uuid_create(UUID_TYPE_DEFAULT)));
        $this->key = $params->getString('key');
        $this->name = $params->getString('name');
        $this->domain = $params->getString('domain');
        $this->created = $params->getInstance('created', \DateTimeInterface::class);
    }

    /**
     * Set id
     *
     * @param string $id
     *
     * @Groups({"me"})
     */
    public function setId(string $id) : self
    {
        $this->id = $id;

        return $this;
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
     * Get Key
     *
     * @Groups({"anonymous"})
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
     *
     * @Groups({"anonymous"})
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
     *
     * @Groups({"anonymous"})
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
