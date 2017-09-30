<?php

namespace App\Tests\Entity\User;

use App\Entity\Location;
use App\Entity\User\Name;
use App\Entity\User\Email;
use App\Entity\User\User;
use App\Tests\Entity\EntityTest;

class UserTest extends EntityTest
{

    public function testSetCreatedValue()
    {
        $user = new User();

        $user->setCreatedValue();

        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreated());
    }

    public function testSetNameUser()
    {
        $user = new User();

        $user->setNameUser();

        $this->assertSame($user, $user->getName()->getUser());
    }

    public function testGetId()
    {
        $id = '897cc71a-e9c4-4f3f-952f-be20bd2a2018';
        $user = new User([
            'id' => $id,
        ]);

        $this->assertEquals($id, $user->getId());
    }

    public function testSetUsername()
    {
        $user = new User();

        $username = 'test';
        $user->setUsername($username);

        $this->assertEquals($username, $user->getUsername());
    }

    public function testGetSalt()
    {
        $user = new User();

        $this->assertNull($user->getSalt());
    }

    public function testGetPassword()
    {
        $user = new User();

        $this->assertNull($user->getPassword());
    }

    public function testGetRoles()
    {
        $user = new User();

        $roles = $user->getRoles();
        $this->assertEquals([
            'ROLE_ANONYMOUS',
            'ROLE_AUTHENTICATED',
        ], $roles);

        $user = new User([
            'name' => [
                'first' => 'Test',
                'last' => 'User',
            ],
            'primaryEmail' => [
                'email' => 'test@example.com',
                'verified' => new \DateTime()
            ],
            'username' => 'test',
            'location' => [
                'id' => '12345',
            ],
        ]);

        $roles = $user->getRoles();
        $this->assertEquals([
            'ROLE_ANONYMOUS',
            'ROLE_AUTHENTICATED',
            'ROLE_STANDARD',
        ], $roles);
    }

    public function testIsNeighbor()
    {
        $user = new User();
        $other = new User();

        $this->assertFalse($user->isNeighbor($other));

        $user = new User([
            'name' => [
                'first' => 'Test',
                'last' => 'User',
            ],
            'primaryEmail' => [
                'email' => 'test@example.com',
                'verified' => new \DateTime()
            ],
            'username' => 'test',
            'location' => [
                'id' => '123',
                'place' => [
                    'id' => 874397665,
                ],
            ],
        ]);

        $other = $user = new User([
            'name' => [
                'first' => 'Other',
                'last' => 'User',
            ],
            'primaryEmail' => [
                'email' => 'other@example.com',
                'verified' => new \DateTime()
            ],
            'username' => 'test',
            'location' => [
                'id' => '456',
                'place' => [
                    'id' => 874397665,
                ],
            ],
        ]);

        $this->assertTrue($user->isNeighbor($other));
    }

    public function testEraseCredentials()
    {
        $user = new User();

        $this->assertNull($user->eraseCredentials());
    }

    public function testSerialize()
    {
        $id = '897cc71a-e9c4-4f3f-952f-be20bd2a2018';
        $user = new User([
            'id' => $id,
        ]);

        $this->assertEquals(serialize([$id]), $user->serialize());
    }

    public function testUnserialize()
    {
        $id = '897cc71a-e9c4-4f3f-952f-be20bd2a2018';
        $user = new User();

        $user->unserialize(serialize([$id]));

        $this->assertEquals($id, $user->getId());
    }

    public function testIsEqualTo()
    {
        $id = '897cc71a-e9c4-4f3f-952f-be20bd2a2018';
        $user = new User([
            'id' => $id,
        ]);

        $this->assertTrue($user->isEqualTo($user));
    }

    public function testSetName()
    {
        $name = $this->getMockBuilder(Name::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = new User();

        $user->setName($name);

        $this->assertSame($name, $user->getName());
    }

    public function testAddEmail()
    {
        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = new User();
        $user->addEmail($email);

        $this->assertSame($email, $user->getEmails()->first());
    }

    public function testRemoveEmail()
    {
        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = new User([
            'emails' => [
                $email,
            ],
        ]);

        $this->assertCount(1, $user->getEmails());

        $user->removeEmail($email);

        $this->assertCount(0, $user->getEmails());
    }

    public function testSetLocation()
    {
        $user = new User();

        $location = $this->getMockBuilder(Location::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user->setLocation($location);

        $this->assertSame($location, $user->getLocation());
    }

    public function testEnabled()
    {
        $user = new User();

        $user->disable();
        $user->enable();

        $this->assertTrue($user->isEnabled());
    }

    public function testSetCreated()
    {
        $user = new User();

        $datetime = new \DateTime();

        $user->setCreated($datetime);

        $this->assertSame($datetime, $user->getCreated());
    }

    public function testGetColor()
    {
        $user = new User([
            'username' => 'test',
        ]);

        $this->assertEquals('#098f6b', $user->getColor());

        $user = new User();

        $this->assertNull($user->getColor());
    }

    public function testGetUser()
    {
        $user = new User();

        $this->assertSame($user, $user->getUser());
    }
}
