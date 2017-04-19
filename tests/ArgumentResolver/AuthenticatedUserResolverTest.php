<?php

namespace GeoSocio\Core\Tests\ArgumentResolver;

use GeoSocio\Core\ArgumentResolver\AuthenticatedUserResolver;
use GeoSocio\Core\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticatedUserResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $resolver = new AuthenticatedUserResolver($tokenStorage);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $argument = $this->getMockBuilder(ArgumentMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $argument->expects($this->once())
            ->method('getType')
            ->willReturn(User::class);
        $argument->expects($this->once())
            ->method('getName')
            ->willReturn('authenticated');

        $result = $resolver->supports($request, $argument);

        $this->assertTrue($result);
    }

    public function testResolve()
    {
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $resolver = new AuthenticatedUserResolver($tokenStorage);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $argument = $this->getMockBuilder(ArgumentMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $resolver->resolve($request, $argument)->current();

        $this->assertSame($user, $result);
    }
}
