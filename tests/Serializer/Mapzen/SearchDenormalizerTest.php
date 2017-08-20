<?php

namespace App\Tests\Serializer\Mapzen;

use App\Entity\Location;
use App\Serializer\Mapzen\SearchDenormalizer;
use PHPUnit\Framework\TestCase;

class SearchDenormalizerTest extends TestCase
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
