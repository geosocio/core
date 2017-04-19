<?php

namespace GeoSocio\Core\Client\Mapzen;

use GeoSocio\Core\Client\Client;
use GeoSocio\Core\Entity\Location;
use GuzzleHttp\Exception\ClientException;

/**
 * Search Client.
 */
class Search extends Client implements SearchInterface
{

    /**
     * {@inheritdoc}
     */
    public function get(string $id) : Location
    {
        $response = null;
        while (!$response) {
            try {
                $response = $this->client->get('place', [
                    'query' => [
                        'ids' => $id
                    ],
                ]);
            } catch (ClientException $e) {
                // Wait a second and try again.
                if ($e->getResponse()->getStatusCode() === 429) {
                    $response = null;
                    sleep(1);
                    continue;
                }

                throw $e;
            }
        }

        return $this->serializer->deserialize((string) $response->getBody(), Location::class, 'json');
    }
}
