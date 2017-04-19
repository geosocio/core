<?php

namespace GeoSocio\Core\Tests;

use GeoSocio\Core\Core;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CoreTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Build Test.
     */
    public function testBuild()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $core = new Core();
        $result = $core->build($container);

        $this->assertNull($result);
    }
}
