<?php

namespace GeoSocio\Core\Client\Mapzen;

use GeoSocio\Core\Client\Client;
use GeoSocio\Core\Entity\Place\Place;
use GuzzleHttp\Exception\ClientException;

/**
 * Who's on First.
 */
class WhosOnFirst extends Client implements WhosOnFirstInterface
{

    /**
     * {@inheritdoc}
     */
    public function get(int $id) : Place
    {
        $response = null;
        while (!$response) {
            try {
                $response = $this->client->get(null, [
                    'query' => [
                        'method' => 'whosonfirst.places.getInfo',
                        'extras' => 'name:,wof:lang,wof:lang_x_official',
                        'id' => $id,
                    ],
                ]);
            } catch (ClientException $e) {
                // Wait a second and try again.
                if ($e->getResponse()->getStatusCode() == 429) {
                    $response = null;
                    sleep(1);
                    continue;
                }

                throw $e;
            }
        }

        return $this->serializer->deserialize((string) $response->getBody(), Place::class, 'json');
    }
}
