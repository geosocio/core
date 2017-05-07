<?php

namespace GeoSocio\Core\Entity;

/**
 * Tree
 */
interface TreeAwareInterface
{
    /**
     * Entity Id.
     */
    public function getId();

    /**
     * Parent Object.
     */
    public function getParent();

    /**
     * Class of the tree entity.
     *
     * @TODO Make this an annotation.
     */
    public function getTreeClass() : string;
}
