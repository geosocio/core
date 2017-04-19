<?php

namespace GeoSocio\Core\Entity\Place;

use GeoSocio\Core\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="place_tree")
 */
class Tree extends Entity
{

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\JoinColumn(name="ancestor", referencedColumnName="place_id")
     * @ORM\ManyToOne(targetEntity="Place")
     */
    private $ancestor;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\JoinColumn(name="descendant", referencedColumnName="place_id")
     * @ORM\ManyToOne(targetEntity="Place")
     */
    private $descendant;

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
        $ancestor = $data['ancestor'] ?? null;
        $this->ancestor = $this->getSingle($ancestor, Place::class);

        $descendant = $data['descendant'] ?? null;
        $this->descendant = $this->getSingle($descendant, Place::class);

        $depth = $data['depth'] ?? null;
        $this->depth = is_integer($depth) ? $depth : null;
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
     * @param Place $ancestor
     */
    public function setAncestor(Place $ancestor) : self
    {
        $this->ancestor = $ancestor;

        return $this;
    }

    /**
     * Get ancestor
     */
    public function getAncestor() :? Place
    {
        return $this->ancestor;
    }

    /**
     * Set descendant
     *
     * @param Place $descendant
     */
    public function setDescendant(Place $descendant) : self
    {
        $this->descendant = $descendant;

        return $this;
    }

    /**
     * Get descendant
     */
    public function getDescendant() :? Place
    {
        return $this->descendant;
    }
}
