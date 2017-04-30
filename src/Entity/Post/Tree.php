<?php

namespace GeoSocio\Core\Entity\Post;

use GeoSocio\Core\Entity\Tree as TreeBase;
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
    private $ancestor;

    /**
     * @var Post
     *
     * @ORM\Id
     * @ORM\JoinColumn(name="descendant", referencedColumnName="post_id")
     * @ORM\ManyToOne(targetEntity="Post")
     */
    private $descendant;

    /**
     * Create new Tree.
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        $ancestor = $data['ancestor'] ?? null;
        $this->ancestor = $this->getSingle($ancestor, Post::class);

        $descendant = $data['descendant'] ?? null;
        $this->descendant = $this->getSingle($descendant, Post::class);
    }
}
