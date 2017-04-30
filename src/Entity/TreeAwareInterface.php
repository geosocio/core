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
     */
    public function getTreeClass() : string;
}
