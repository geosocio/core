<?php

namespace App\Tests\Repository;

use App\Entity\User\User;
use App\Entity\User\Email;
use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
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
