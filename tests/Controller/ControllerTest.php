<?php

namespace GeoSocio\Core\Tests\Controller;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use GeoSocio\Core\Utils\EntityAttacherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

abstract class ControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    const FORMAT = 'json';

    /**
     * Gets the mock serializer.
     */
    protected function getDenormalizer()
    {
        return $this->createMock(DenormalizerInterface::class);
    }

    /**
     * Gets the mock doctrine.
     */
    protected function getDoctrine()
    {
        return $this->createMock(RegistryInterface::class);
    }

    /**
     * Gets the mock entity manager.
     */
    protected function getEntityManager()
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    /**
     * Gets the mock entity attacher.
     */
    protected function getEntityAttacher()
    {
        return $this->createMock(EntityAttacherInterface::class);
    }

    /**
     * Gets the mock entity manager.
     */
    protected function getRepository()
    {
        return $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
