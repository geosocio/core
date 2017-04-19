<?php

namespace GeoSocio\Core\Tests\Serializer\Mapzen;

use GeoSocio\Core\Entity\Location;
use GeoSocio\Core\Serializer\Mapzen\SearchDenormalizer;

class SearchDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    public function testDenormalize()
    {
        $gid = "whosonfirst:neighbourhood:874397665";
        $data = [
            'features' => [
                [
                    "properties" => [
                            "gid" => $gid,
                    ],
                ],
            ],
        ];
        $normalizer = new SearchDenormalizer();
        $location = $normalizer->denormalize($data, Location::class, 'test');

        $this->assertEquals($gid, $location->getId());
    }
}
