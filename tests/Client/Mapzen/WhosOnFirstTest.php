<?php

namespace App\Test\Client\Mapzen;

use App\Client\Mapzen\WhosOnFirst;
use App\Entity\Place\Place;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Tests\Client\ClientTest;

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

        $promise = new FulfilledPromise($response);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
               ->method('requestAsync')
               ->willReturn($promise);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->once())
                  ->method('deserialize')
                  ->willReturn($place);

        $search = new WhosOnFirst($client, $serialzer);
        $response = $search->get($id)->wait();

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
               ->method('requestAsync')
               ->willThrowException($exception);

        $serialzer = $this->createMock(SerializerInterface::class);
        $serialzer->expects($this->never())
                  ->method('deserialize');

        $search = new WhosOnFirst($client, $serialzer);

        $this->expectException(ClientException::class);
        $response = $search->get($id);
    }
}
