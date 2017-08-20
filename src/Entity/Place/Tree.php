<?php

namespace App\Entity\Place;

use App\Entity\Tree as TreeBase;
use GeoSocio\EntityUtils\ParameterBag;
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
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $params = new ParameterBag($data);
        $this->ancestor = $params->getInstance('ancestor', Place::class);
        $this->descendant = $params->getInstance('descendant', Place::class);
    }
}
