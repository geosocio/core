<?php

namespace App\Tests\Utils;

use App\Entity\Location;
use App\Client\Mapzen\SearchInterface;
use App\Client\Mapzen\WhosOnFirstInterface;
use App\Entity\Place\Place;
use App\Entity\Place\Tree;
use App\Utils\PlaceFinder;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use PHPUnit\Framework\TestCase;
use GeoSocio\Slugger\SluggerInterface;

/**
 * Array Utilties Test.
 */
class PlaceFinderTest extends TestCase
{
    /**
     * Tests the find method.
     */
    public function testFind()
    {

        $place_id = 321;
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getId')
            ->willReturn($place_id);
        $place->method('getName')
            ->willReturn('Orlando');

        $id = '123';
        $location = $this->getMockBuilder(Location::class)
            ->disableOriginalConstructor()
            ->getMock();
        $location->method('getId')
            ->willReturn($id);
        $location->method('getPlace')
            ->willReturn($place);

        $locationRepository = $this->createMock(ObjectRepository::class);

        $placeRepository = $this->createMock(ObjectRepository::class);
        $placeRepository->method('find')
            ->with($place_id)
            ->willReturn($place);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->willReturnMap([
                [
                    Location::class,
                    $locationRepository,
                ],
                [
                    Place::class,
                    $placeRepository,
                ],
            ]);

        $doctrine = $this->createMock(RegistryInterface::class);
        $doctrine->method('getEntityManager')
            ->willReturn($em);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($location);

        $search = $this->createMock(SearchInterface::class);
        $search->method('get')
            ->with($id)
            ->willReturn($promise);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($place);

        $whosonfirst = $this->createMock(WhosOnFirstInterface::class);
        $whosonfirst->method('get')
            ->with($place_id)
            ->willReturn($promise);

        $slugger = $this->createMock(SluggerInterface::class);

        $placeFinder = new PlaceFinder($doctrine, $search, $whosonfirst, $slugger);

        $result = $placeFinder->find($location);
        $this->assertInstanceOf(Location::class, $result);

        $this->resetCount();

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')
            ->willReturn(404);

        $exception = $this->getMockBuilder(ClientException::class)
            ->disableOriginalConstructor()
            ->getMock();
        $exception->method('getResponse')
            ->willReturn($response);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($place);

        $whosonfirst
            ->method('get')
            ->with($place_id)
            ->willReturnOnConsecutiveCalls(
                $this->throwException($exception),
                $this->returnValue($promise)
            );

        $tree = $this->getMockBuilder(Tree::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tree->method('getAncestor')
            ->willReturn($place);

        $ancestor = $this->createMock(Collection::class);
        $ancestor->method('first')
            ->willReturn($tree);

        $place->method('getAncestors')
            ->willReturn($ancestor);

        $result = $placeFinder->find($location);
        $this->assertInstanceOf(Location::class, $result);

        $this->resetCount();

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($place);

        $whosonfirst
            ->method('get')
            ->with($place_id)
            ->willReturnOnConsecutiveCalls(
                $this->throwException($exception),
                $this->throwException($exception),
                $this->returnValue($promise)
            );

        $ancestor->expects($this->once())
            ->method('next')
            ->willReturn($tree);

        $result = $placeFinder->find($location);
        $this->assertInstanceOf(Location::class, $result);
    }

    /**
     * Tests the get method by testing find.
     */
    public function testGetPlace()
    {
        $place_id = 321;
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getId')
            ->willReturn($place_id);
        $place->method('getName')
            ->willReturn('Orlando');

        $id = '123';
        $location = $this->getMockBuilder(Location::class)
            ->disableOriginalConstructor()
            ->getMock();
        $location->method('getId')
            ->willReturn($id);
        $location->method('getPlace')
            ->willReturn($place);

        $locationRepository = $this->createMock(ObjectRepository::class);

        $placeRepository = $this->createMock(ObjectRepository::class);
        $placeRepository->method('find')
            ->with($place_id)
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(null),
                $this->returnValue($place)
            );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->willReturnMap([
                [
                    Location::class,
                    $locationRepository,
                ],
                [
                    Place::class,
                    $placeRepository,
                ],
            ]);

        $doctrine = $this->createMock(RegistryInterface::class);
        $doctrine->method('getEntityManager')
            ->willReturn($em);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($location);

        $search = $this->createMock(SearchInterface::class);
        $search->method('get')
            ->with($id)
            ->willReturn($promise);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($place);

        $whosonfirst = $this->createMock(WhosOnFirstInterface::class);
        $whosonfirst->method('get')
            ->with($place_id)
            ->willReturn($promise);

        $slugger = $this->createMock(SluggerInterface::class);

        $placeFinder = new PlaceFinder($doctrine, $search, $whosonfirst, $slugger);

        $result = $placeFinder->find($location);
        $this->assertInstanceOf(Location::class, $result);
    }

    /**
     * Tests the get method by testing find.
     */
    public function testGetPlaceDuplicateSlug()
    {
        $place_id = 321;
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getId')
            ->willReturn($place_id);
        $place->method('getName')
            ->willReturn('Orlando');

        $id = '123';
        $location = $this->getMockBuilder(Location::class)
            ->disableOriginalConstructor()
            ->getMock();
        $location->method('getId')
            ->willReturn($id);
        $location->method('getPlace')
            ->willReturn($place);

        $locationRepository = $this->createMock(ObjectRepository::class);

        $placeRepository = $this->createMock(ObjectRepository::class);
        $placeRepository->method('find')
            ->with($place_id)
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(null),
                $this->returnValue($place)
            );

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->willReturnMap([
                [
                    Location::class,
                    $locationRepository,
                ],
                [
                    Place::class,
                    $placeRepository,
                ],
            ]);

        $exception = $this->getMockBuilder(UniqueConstraintViolationException::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em->method('flush')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException($exception),
                null
            );

        $doctrine = $this->createMock(RegistryInterface::class);
        $doctrine->method('getEntityManager')
            ->willReturn($em);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($location);

        $search = $this->createMock(SearchInterface::class);
        $search->method('get')
            ->with($id)
            ->willReturn($promise);

        $promise = $this->createMock(PromiseInterface::class);
        $promise->method('wait')
            ->willReturn($place);

        $whosonfirst = $this->createMock(WhosOnFirstInterface::class);
        $whosonfirst->method('get')
            ->with($place_id)
            ->willReturn($promise);

        $slugger = $this->createMock(SluggerInterface::class);

        $placeFinder = new PlaceFinder($doctrine, $search, $whosonfirst, $slugger);

        $result = $placeFinder->find($location);
        $this->assertInstanceOf(Location::class, $result);
    }
}
