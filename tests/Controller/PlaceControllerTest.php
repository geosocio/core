<?php

namespace GeoSocio\Core\Tests\Controller;

use GeoSocio\Core\Controller\PlaceController;
use GeoSocio\Core\Entity\Place\Place;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PlaceControllerTest extends ControllerTest
{
    public function testIndexAction()
    {

        $denormalizer = $this->getDenormalizer();

        $slug = 'orlando';
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('__call')
            ->with('findOneBySlug', [$slug])
            ->willReturn($place);

        $doctrine = $this->getDoctrine();
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with(Place::class)
            ->willReturn($repository);

        $entityAttacher = $this->getEntityAttacher();

        $controller = new PlaceController($denormalizer, $doctrine, $entityAttacher);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->query = $this->createMock(ParameterBagInterface::class);
        $request->query->expects($this->once())
            ->method('has')
            ->with('slug')
            ->willReturn(true);
        $request->query->expects($this->once())
            ->method('get')
            ->with('slug')
            ->willReturn($slug);

        $response = $controller->indexAction($request);

        $this->assertInstanceOf(Place::class, $response);
    }
}
