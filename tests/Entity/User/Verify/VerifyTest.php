<?php

namespace GeoSocio\Core\Tests\Entity\User\Verify;

use GeoSocio\Core\Tests\Entity\EntityTest;

abstract class VerifyTest extends EntityTest
{

    public function testSetCreatedValue()
    {
        $verify = new $this->class();

        $this->assertNull($verify->getCreated());

        $verify->setCreatedValue();

        $this->assertInstanceOf(\DateTimeInterface::class, $verify->getCreated());
    }

    public function testSetToken()
    {
        $verify = new $this->class();

        $this->assertNull($verify->getToken());

        $token = 'abc';
        $verify->setToken($token);

        $this->assertEquals($token, $verify->getToken());
    }

    public function testSetCode()
    {
        $verify = new $this->class();

        $this->assertNull($verify->getCode());

        $code = '123';
        $verify->setCode($code);

        $this->assertEquals($code, $verify->getCode());
    }

    public function testSetCreated()
    {
        $verify = new $this->class();

        $datetime = new \DateTime();

        $verify->setCreated($datetime);

        $this->assertSame($datetime, $verify->getCreated());
    }

    public function testIsEqualTo()
    {
        $data = [
            'token' => 'abc',
            'code' => '123',
        ];

        $verify = new $this->class($data);

        $this->assertFalse($verify->isEqualTo($verify));

        $verify->hashData();

        $this->assertTrue($verify->isEqualTo($verify));
    }

    public function testIsFresh()
    {
        $verify = new $this->class([
            'created' => new \DateTime(),
        ]);

        $this->assertTrue($verify->isFresh());
    }
}
