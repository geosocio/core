<?php

namespace GeoSocio\Core\Test\Client\Mapzen;

use GeoSocio\Core\Client\Mapzen\WhosOnFirst;
use GeoSocio\Core\Entity\Place\Place;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\MessageInterface;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;
use GeoSocio\Core\Tests\Client\ClientTest;

class WhosOnFirstTest extends ClientTest
{

    /**
     * Tests the Get method.
     */
    public function testGet()
    {
        $id = 1234;
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getId')
            ->willReturn($id);

        $response = $this->createMock(MessageInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
               ->method('get')
               ->willReturn($response);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->once())
                  ->method('deserialize')
                  ->willReturn($place);

        $search = new WhosOnFirst($client, $serialzer);
        $response = $search->get($id);

        $this->assertInstanceOf(Place::class, $response);
        $this->assertEquals($id, $response->getId());
    }

    /**
     * Test the first response being a bad response.
     */
    public function testGetLoop()
    {
        $id = 1234;
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getId')
            ->willReturn($id);

        $request = $this->createMock(RequestInterface::class);
        $badResponse = $this->createMock(ResponseInterface::class);
        $badResponse->method('getStatusCode')
                    ->willReturn(429);
        $exception = new ClientException('Too Many Requests', $request, $badResponse);
        $response = $this->createMock(MessageInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))
               ->method('get')
               ->willReturnOnConsecutiveCalls($this->throwException($exception), $this->returnValue($response));

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->once())
                  ->method('deserialize')
                  ->willReturn($place);

        $search = new WhosOnFirst($client, $serialzer);
        $response = $search->get($id);

        $this->assertInstanceOf(Place::class, $response);
        $this->assertEquals($id, $response->getId());
    }

    /**
     * Test a complete failure of some kind.
     */
    public function testGetFailure()
    {
        $id = 1234;
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getId')
            ->willReturn($id);

        $request = $this->createMock(RequestInterface::class);
        $badResponse = $this->createMock(ResponseInterface::class);
        $badResponse->method('getStatusCode')
                    ->willReturn(500);
        $exception = new ClientException('Server Error', $request, $badResponse);
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
               ->method('get')
               ->willThrowException($exception);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->never())
                  ->method('deserialize');

        $search = new WhosOnFirst($client, $serialzer);

        $this->expectException(ClientException::class);
        $response = $search->get($id);
    }
}
