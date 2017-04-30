<?php

namespace GeoSocio\Core\Entity;

/**
 * Tree
 */
interface TreeInterface
{
    /**
     * Set depth
     */
    public function setDepth(int $depth);

    /**
     * Get depth
     */
    public function getDepth() :? int;

    /**
     * Set ancestor
     */
    public function setAncestor($ancestor);

    /**
     * Get ancestor
     */
    public function getAncestor();

    /**
     * Set descendant
     */
    public function setDescendant($descendant);

    /**
     * Get descendant
     */
    public function getDescendant();
}
