<?php

namespace App\Client\Mapzen;

use App\Client\Client;
use App\Entity\Location;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Search Client.
 */
class Search extends Client implements SearchInterface
{

    /**
     * {@inheritdoc}
     */
    public function get(string $id) : PromiseInterface
    {
        return $this->client->requestAsync('GET', 'place', [
            'query' => [
                'ids' => $id
            ],
        ])->then(function ($response) {
            return $this->serializer->deserialize((string) $response->getBody(), Location::class, 'json');
        });
    }
}
