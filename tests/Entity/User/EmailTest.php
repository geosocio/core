<?php

namespace App\Tests\Entity\User;

use App\Entity\User\Email;
use App\Entity\User\User;
use App\Tests\Entity\EntityTest;

class EmailTest extends EntityTest
{
    public function testSetCreatedValue()
    {
        $email = new Email();

        $email->setCreatedValue();

        $this->assertInstanceOf(\DateTimeInterface::class, $email->getCreated());
    }

    public function testSetEmail()
    {
        $address = 'test@example.com';
        $email = new Email();

        $email->setEmail($address);

        $this->assertEquals($address, $email->getEmail());
    }

    public function testSetUser()
    {
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $email = new Email();

        $email->setUser($user);

        $this->assertSame($user, $email->getUser());
    }

    public function testSetCreated()
    {
        $email = new Email();

        $datetime = new \DateTime();

        $email->setCreated($datetime);

        $this->assertSame($datetime, $email->getCreated());
    }

    public function testSetVerified()
    {
        $email = new Email();

        $datetime = new \DateTime();

        $email->setVerified($datetime);

        $this->assertSame($datetime, $email->getVerified());
    }

    public function testToString()
    {
        $address = 'test@example.com';
        $email = new Email([
            'email' => $address,
        ]);

        $this->assertEquals($address, (string) $email);
    }

    public function testIsEqualTo()
    {
        $email = new Email([
            'email' => 'test@example.com',
        ]);

        $this->assertTrue($email->isEqualTo($email));
    }
}
