<?php

namespace App\Entity\Post;

use App\Entity\Tree as TreeBase;
use GeoSocio\EntityUtils\ParameterBag;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="post_tree")
 */
class Tree extends TreeBase
{

    /**
     * @var Post
     *
     * @ORM\Id
     * @ORM\JoinColumn(name="ancestor", referencedColumnName="post_id")
     * @ORM\ManyToOne(targetEntity="Post")
     */
    protected $ancestor;

    /**
     * @var Post
     *
     * @ORM\Id
     * @ORM\JoinColumn(name="descendant", referencedColumnName="post_id")
     * @ORM\ManyToOne(targetEntity="Post")
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
        $this->ancestor = $params->getInstance('ancestor', Post::class);
        $this->descendant = $params->getInstance('descendant', Post::class);
    }
}
