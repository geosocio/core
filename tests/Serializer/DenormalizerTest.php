<?php

namespace App\Tests\Serializer;

use App\GroupResolver\GroupResolverInterface;
use App\Serializer\Denormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Serializer Test.
 */
class DenormalizerTest extends TestCase
{
    /**
     * Tests the Request method.
     */
    public function testDenormalize()
    {
        $data = new \stdClass();
        $data->id = 123;
        $input = [
            "id" => $data->id,
        ];

        $d = $this->createMock(DenormalizerInterface::class);
        $d->expects($this->once())
            ->method('denormalize')
            ->with($input, \stdClass::class)
            ->willReturn($data);

        $validator = $this->createMock(ValidatorInterface::class);
        $groupResolver = $this->createMock(GroupResolverInterface::class);
        $denormalizer = new Denormalizer($d, $validator, $groupResolver);

        $result = $denormalizer->denormalize($input, \stdClass::class);

        $this->assertEquals($data, $result);
    }
}
