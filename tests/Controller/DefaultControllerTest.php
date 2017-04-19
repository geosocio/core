<?php

namespace GeoSocio\Core\Tests\Controller;

use GeoSocio\Core\Controller\DefaultController;

class DefaultControllerTest extends ControllerTest
{

    /**
     * Tests the index action.
     */
    public function testIndexAction()
    {
        $data = [
            'hello' => 'world!',
        ];

        $default = new DefaultController($this->getDenormalizer(), $this->getDoctrine());
        $result = $default->indexAction();

        $this->assertEquals($data, $result);
    }
}
