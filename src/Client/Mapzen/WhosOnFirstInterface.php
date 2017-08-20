<?php

namespace App\Client\Mapzen;

use GuzzleHttp\Promise\PromiseInterface;

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
    public function get(int $id) : PromiseInterface;
}
