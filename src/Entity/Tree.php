<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityUtils\ParameterBag;

/**
 * Tree
 * @ORM\MappedSuperclass
 */
abstract class Tree implements TreeInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $depth;

    /**
     * Create new Tree.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->depth = $params->getInt('depth');
    }

    /**
     * Set depth
     *
     * @param int $depth
     */
    public function setDepth(int $depth) : self
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Get depth
     */
    public function getDepth() :? int
    {
        return $this->depth;
    }

    /**
     * Set ancestor
     *
     * @param object $ancestor
     */
    public function setAncestor($ancestor) : self
    {
        $this->ancestor = $ancestor;

        return $this;
    }

    /**
     * Get ancestor
     */
    public function getAncestor()
    {
        return $this->ancestor;
    }

    /**
     * Set descendant
     *
     * @param object $descendant
     */
    public function setDescendant($descendant) : self
    {
        $this->descendant = $descendant;

        return $this;
    }

    /**
     * Get descendant
     */
    public function getDescendant()
    {
        return $this->descendant;
    }
}
