<?php

namespace GeoSocio\Core\Entity\Place;

use GeoSocio\Core\Entity\Tree as TreeBase;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="place_tree")
 */
class Tree extends TreeBase
{

    /**
     * @var Place
     *
     * @ORM\Id
     * @ORM\JoinColumn(name="ancestor", referencedColumnName="place_id")
     * @ORM\ManyToOne(targetEntity="Place")
     */
    protected $ancestor;

    /**
     * @var Place
     *
     * @ORM\Id
     * @ORM\JoinColumn(name="descendant", referencedColumnName="place_id")
     * @ORM\ManyToOne(targetEntity="Place")
     */
    protected $descendant;

    /**
     * Create new Tree.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $ancestor = $data['ancestor'] ?? null;
        $this->ancestor = $this->getSingle($ancestor, Place::class);

        $descendant = $data['descendant'] ?? null;
        $this->descendant = $this->getSingle($descendant, Place::class);
    }
}
