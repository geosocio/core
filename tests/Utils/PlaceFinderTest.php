<?php

namespace GeoSocio\Core\Tests\Utils;

use GeoSocio\Core\Entity\Location;
use GeoSocio\Core\Client\Mapzen\SearchInterface;
use GeoSocio\Core\Client\Mapzen\WhosOnFirstInterface;
use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Entity\Place\Tree;
use GeoSocio\Core\Utils\PlaceFinder;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Array Utilties Test.
 */
class PlaceFinderTest extends \PHPUnit_Framework_TestCase
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

        $search = $this->createMock(SearchInterface::class);
        $search->method('get')
            ->with($id)
            ->willReturn($location);

        $whosonfirst = $this->createMock(WhosOnFirstInterface::class);
        $whosonfirst->method('get')
            ->with($place_id)
            ->willReturn($place);

        $placeFinder = new PlaceFinder($doctrine, $search, $whosonfirst);

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

        $whosonfirst
            ->method('get')
            ->with($place_id)
            ->willReturnOnConsecutiveCalls(
                $this->throwException($exception),
                $this->returnValue($place)
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

        $whosonfirst
            ->method('get')
            ->with($place_id)
            ->willReturnOnConsecutiveCalls(
                $this->throwException($exception),
                $this->throwException($exception),
                $this->returnValue($place)
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

        $search = $this->createMock(SearchInterface::class);
        $search->method('get')
            ->with($id)
            ->willReturn($location);

        $whosonfirst = $this->createMock(WhosOnFirstInterface::class);
        $whosonfirst->method('get')
            ->with($place_id)
            ->willReturn($place);

        $placeFinder = new PlaceFinder($doctrine, $search, $whosonfirst);

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

        $search = $this->createMock(SearchInterface::class);
        $search->method('get')
            ->with($id)
            ->willReturn($location);

        $whosonfirst = $this->createMock(WhosOnFirstInterface::class);
        $whosonfirst->method('get')
            ->with($place_id)
            ->willReturn($place);

        $placeFinder = new PlaceFinder($doctrine, $search, $whosonfirst);

        $result = $placeFinder->find($location);
        $this->assertInstanceOf(Location::class, $result);
    }
}
