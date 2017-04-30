<?php

namespace GeoSocio\Core\Entity;

use Doctrine\ORM\Mapping as ORM;

trait CreatedTrait
{
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue() : self
    {
        $this->created = new \DateTime();

        return $this;
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
}
