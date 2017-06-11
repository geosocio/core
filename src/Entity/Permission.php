<?php

namespace GeoSocio\Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * GeoSocio\Entity\Location
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="permission")
 */
class Permission extends Entity
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
        $id = $data['id'] ?? null;
        $this->id = is_string($id) ? $id  : null;

        $name = $data['name'] ?? null;
        $this->name = is_string($name) ? $name : null;
    }

    /**
     * Get id
     *
     * @Groups({"anonymous"})
     */
    public function getId() :? string
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @Groups({"me"})
     */
    public function setId(string $id) : self
    {
        $this->id = $id;

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
}
