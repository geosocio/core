<?php

namespace App\Client\Mapzen;

use GuzzleHttp\Promise\PromiseInterface;

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
    public function get(string $id) : PromiseInterface;
}
