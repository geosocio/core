<?php

namespace App\Client\Mapzen;

use App\Client\Client;
use App\Entity\Location;
use GuzzleHttp\Psr7\Request;
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
            'headers' => $this->client->getConfig('headers'),
            'query' => array_merge(
                $this->client->getConfig('query'),
                [
                    'ids' => $id
                ]
            ),
        ])->then(function ($response) {
            $locations = $this->serializer->deserialize((string) $response->getBody(), Location::class, 'json');
            return count($locations) > 0 ? $locations[0] : new Location();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $text) : PromiseInterface
    {
        return $this->client->requestAsync('GET', 'autocomplete', [
            'headers' => $this->client->getConfig('headers'),
            'query' => array_merge(
                $this->client->getConfig('query'),
                [
                    'text' => $text
                ]
            ),
        ])->then(function ($response) {
            return $this->serializer->deserialize((string) $response->getBody(), Location::class, 'json');
        });
    }
}
