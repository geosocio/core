<?php

namespace App\Test\Client\Mapzen;

use App\Client\Mapzen\Search;
use App\Entity\Location;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Tests\Client\ClientTest;

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

        $promise = new FulfilledPromise($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
               ->method('requestAsync')
               ->willReturn($promise);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->once())
                  ->method('deserialize')
                  ->willReturn($location);

        $search = new Search($client, $serialzer);
        $response = $search->get($id)->wait();

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
               ->method('requestAsync')
               ->willThrowException($exception);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->never())
                  ->method('deserialize');

        $search = new Search($client, $serialzer);

        $this->expectException(ClientException::class);
        $response = $search->get($id);
    }
}
