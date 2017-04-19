<?php

namespace GeoSocio\Core\Client\Mapzen;

use GeoSocio\Core\Entity\Place\Place;

/**
 * Who's on First.
 */
interface WhosOnFirstInterface
{

    /**
     * Get a place by id.
     *
     * @param int $id
     */
    public function get(int $id) : Place;
}
