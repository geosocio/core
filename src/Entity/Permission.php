<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityUtils\ParameterBag;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * GeoSocio\Entity\Location
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="permission")
 */
class Permission
{

    /**
     * @var int
     *
     * @ORM\Column(name="permission_id", length=7, type="string")
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * Create new Location.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->id = $params->getString('id');
        $this->name = $params->getString('name');
    }

    /**
     * Get id
     *
     * @Groups({"read_anonymous"})
     */
    public function getId() :? string
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param string $id
     *
     * @Groups({"write_me"})
     */
    public function setId(string $id) : self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get Name
     *
     * @Groups({"read_anonymous"})
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
}
