<?php

namespace GeoSocio\Core\Tests\Utils;

use GeoSocio\Core\Utils\ArrayUtils;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Array Utilties Test.
 */
class ArrayUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the search utility.
     *
     * @dataProvider searches
     *
     * @param mixed $id
     * @param array $data
     */
    public function testSearch($id, $data)
    {
        $result = ArrayUtils::search($data, function ($item) use ($id) {
            return $id === $item;
        });

        $this->assertEquals($id, $result);

        $result = ArrayUtils::search($data, function ($item) {
            return 'fake' === $item;
        });

        $this->assertNull($result);
    }

    /**
     * Data provider for slug test.
     */
    public function searches() : array
    {
        return [
            [
                12345,
                [
                    12345
                ]
            ],
            [
                12345,
                new ArrayCollection([12345]),
            ]
        ];
    }
}
