<?php

namespace GeoSocio\Core\Tests\Repository;

use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\Email;
use GeoSocio\Core\Repository\User\UserRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;

class UserRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromEmail()
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $class = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = new UserRepository($em, $class);

        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->getMock();

        $user = $repository->createFromEmail($email);

        $this->assertInstanceOf(User::class, $user);
    }
}
