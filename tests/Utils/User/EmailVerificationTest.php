<?php

namespace GeoSocio\Core\Tests\Utils\User;

use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\Email;
use GeoSocio\Core\Repository\User\UserRepository;
use GeoSocio\Core\Utils\Dispatcher\DispatcherInterface;
use GeoSocio\Core\Utils\User\EmailVerification;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use RandomLib\Generator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class EmailVerificationTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $userRespository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($userRespository);

        $emailRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine = $this->createMock(RegistryInterface::class);
        $doctrine->expects($this->exactly(2))
            ->method('getManager')
            ->willReturn($em);

        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with(Email::class)
            ->willReturn($emailRepository);

        $random = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher = $this->createMock(DispatcherInterface::class);

        $emailVerification = new EmailVerification($doctrine, $random, $dispatcher);

        $email = 'test@example.com';
        $verify = $emailVerification->create($email);

        $this->assertEquals($email, $verify->getEmail()->getEmail());
    }
}
