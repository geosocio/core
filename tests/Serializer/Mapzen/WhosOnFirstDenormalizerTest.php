<?php

namespace App\Tests\Serializer\Mapzen;

use App\Entity\Place\Place;
use App\Serializer\Mapzen\WhosOnFirstDenormalizer;
use PHPUnit\Framework\TestCase;

class WhosOnFirstDenormalizerTest extends TestCase
{
    public function testSupportsDenormalization()
    {
        $denormalizer = new WhosOnFirstDenormalizer();

        $data = [
            'place' => [],
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
            'place' => [
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
