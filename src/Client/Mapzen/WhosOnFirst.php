<?php

namespace App\Client\Mapzen;

use App\Client\Client;
use App\Entity\Place\Place;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Who's on First.
 */
class WhosOnFirst extends Client implements WhosOnFirstInterface
{

    /**
     * {@inheritdoc}
     */
    public function get(int $id) : PromiseInterface
    {
        return $this->client->requestAsync('GET', null, [
            'query' => [
                'method' => 'whosonfirst.places.getInfo',
                'extras' => 'name:,wof:lang,wof:lang_x_official',
                'id' => $id,
            ],
        ])->then(function ($response) {
            return $this->serializer->deserialize((string) $response->getBody(), Place::class, 'json');
        });
    }
}
