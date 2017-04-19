<?php

namespace GeoSocio\Core\Test\Client\Mapzen;

use GeoSocio\Core\Client\Mapzen\Search;
use GeoSocio\Core\Entity\Location;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\MessageInterface;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;
use GeoSocio\Core\Tests\Client\ClientTest;

class SearchTest extends ClientTest
{

    /**
     * Tests the Get method.
     */
    public function testGet()
    {
        $id = '1234';
        $location = $this->getMockBuilder(Location::class)
            ->disableOriginalConstructor()
            ->getMock();
        $location->method('getId')
            ->willReturn($id);

        $response = $this->createMock(MessageInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
               ->method('get')
               ->willReturn($response);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->once())
                  ->method('deserialize')
                  ->willReturn($location);

        $search = new Search($client, $serialzer);
        $response = $search->get($id);

        $this->assertInstanceOf(Location::class, $response);
        $this->assertEquals($id, $response->getId());
    }

    /**
     * Test the first response being a bad response.
     */
    public function testGetLoop()
    {
        $id = '1234';
        $location = $this->getMockBuilder(Location::class)
            ->disableOriginalConstructor()
            ->getMock();
        $location->method('getId')
            ->willReturn($id);

        $badResponse = $this->createMock(ResponseInterface::class);
        $badResponse->method('getStatusCode')
                    ->willReturn(429);

        $exception = $this->getMockBuilder(ClientException::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exception->method('getResponse')
            ->willReturn($badResponse);

        $response = $this->createMock(MessageInterface::class);
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->exactly(2))
               ->method('get')
               ->willReturnOnConsecutiveCalls($this->throwException($exception), $this->returnValue($response));

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->once())
                  ->method('deserialize')
                  ->willReturn($location);

        $search = new Search($client, $serialzer);
        $response = $search->get($id);

        $this->assertInstanceOf(Location::class, $response);
        $this->assertEquals($id, $response->getId());
    }

    /**
     * Test a complete failure of some kind.
     */
    public function testGetFailure()
    {
        $id = '1234';
        $location = $this->getMockBuilder(Location::class)
            ->disableOriginalConstructor()
            ->getMock();
        $location->method('getId')
            ->willReturn($id);

        $badResponse = $this->createMock(ResponseInterface::class);
        $badResponse->method('getStatusCode')
                    ->willReturn(500);

        $exception = $this->getMockBuilder(ClientException::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exception->method('getResponse')
            ->willReturn($badResponse);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
               ->method('get')
               ->willThrowException($exception);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->never())
                  ->method('deserialize');

        $search = new Search($client, $serialzer);

        $this->expectException(ClientException::class);
        $response = $search->get($id);
    }
}
