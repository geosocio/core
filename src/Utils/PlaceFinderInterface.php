<?php

namespace App\Utils;

use App\Entity\Location;

/**
 * Place Finder.
 */
interface PlaceFinderInterface
{
    /**
     * Get a fully loaded location from an input Location.
     *
     * @param Location $input
     */
    public function find(Location $input) : Location;
}
