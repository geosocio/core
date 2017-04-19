<?php

namespace GeoSocio\Core\Tests\EventListener;

use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\EventListener\ReturnListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ReturnListenerTest extends \PHPUnit_Framework_TestCase
{

    public function testOnKernelView()
    {
        $serializer = $this->createMock(SerializerInterface::class);

        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getUser')
            ->willReturnSelf();
        $user->method('getRoles')
            ->with($user)
            ->willReturn([]);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($user);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')
            ->willReturn($token);

        $listener = new ReturnListener($serializer, $tokenStorage);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->once())
            ->method('getRequestFormat')
            ->willReturn('test');

        $event = $this->createMock(GetResponseForControllerResultEvent::class);
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);
        $event->expects($this->once())
            ->method('getControllerResult')
            ->willReturn($user);

        $response = $listener->onKernelView($event);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testOnKernelException()
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $listener = new ReturnListener($serializer, $tokenStorage);

        $exception = $this->getMockBuilder(\Exception::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->exactly(2))
            ->method('getRequestFormat')
            ->willReturn('test');

        $event = $this->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getException')
            ->willReturn($exception);

        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $response = $listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $response);
    }
}
