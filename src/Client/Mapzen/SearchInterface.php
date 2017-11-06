<?php

namespace App\Client\Mapzen;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * Executing a Search on Mapzen.
 */
interface SearchInterface
{
    /**
     * Search for a place.
     *
     * @param string $text
     */
    public function search(string $text) : PromiseInterface;

    /**
     * Get a place by id.
     *
     * @param string $id
     */
    public function get(string $id) : PromiseInterface;
}
