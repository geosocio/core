<?php

namespace GeoSocio\Core\Client\Mapzen;

use GeoSocio\Core\Entity\Location;

/**
 * Executing a Search on Mapzen.
 */
interface SearchInterface
{

    /**
     * Get a place by id.
     *
     * @param string $id
     */
    public function get(string $id) : Location;
}
