<?php

namespace App\Tests\Serializer;

use App\Serializer\Denormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $denormalizer = new Denormalizer($d, $validator, $tokenStorage);

        $result = $denormalizer->denormalize($input, \stdClass::class);

        $this->assertEquals($data, $result);
    }
}
