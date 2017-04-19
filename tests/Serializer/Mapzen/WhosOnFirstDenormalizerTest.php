<?php

namespace GeoSocio\Core\Tests\Serializer\Mapzen;

use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Serializer\Mapzen\WhosOnFirstDenormalizer;

class WhosOnFirstDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsDenormalization()
    {
        $denormalizer = new WhosOnFirstDenormalizer();

        $data = [
            'record' => [],
        ];

        $this->assertTrue($denormalizer->supportsDenormalization($data, Place::class));
        $this->assertFalse($denormalizer->supportsDenormalization([], Place::class));
        $this->assertFalse($denormalizer->supportsDenormalization($data, \stdClass::class));
    }

    public function testDenormalize()
    {
        $denormalizer = new WhosOnFirstDenormalizer();

        $id = 1;
        $parent_id = 0;
        $name = 'Null Island';
        $data = [
            'record' => [
                'wof:id' => $id,
                'wof:parent_id' => $parent_id,
                'wof:lang_x_official' => [
                    'eng',
                ],
                'wof:lang' => [
                    'eng',
                ],
                'wof:name' => '',
                'name:eng_x_preferred' => [
                    $name,
                ],
            ],
        ];

        $place = $denormalizer->denormalize($data, Place::class);

        $this->assertEquals($id, $place->getId());
        $this->assertEquals($parent_id, $place->getParent()->getId());
        $this->assertEquals($name, $place->getName());
    }
}
