<?php

namespace GeoSocio\Core\Tests\Utils;

use GeoSocio\Core\Utils\Slug;

class SlugTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider slugs
     *
     * @param string $text
     * @param string $slug
     */
    public function testSlug(string $text, string $slug) : void
    {
        $created = Slug::create($text);

        $this->assertEquals($slug, $created);
    }

    /**
     * Data provider for slug test.
     */
    public function slugs() : array
    {
        return [
            [
                'Orlando',
                'orlando',
            ],
            [
                'Orlando-',
                'orlando',
            ],
            [
                'Saint Petersburg',
                'saint-petersburg',
            ],
            [
                'Saint  Petersburg',
                'saint-petersburg'
            ],
            [
                'St. Petersburg',
                'st-petersburg',
            ],
            [
                'Orléans',
                'orléans',
            ],
            [
                'Āhualoa',
                'āhualoa',
            ],
            [
                'Hōnaunau-Napoʻopoʻo',
                'hōnaunau-napoʻopoʻo',
            ],
            [
                'Béal Feirste',
                'béal-feirste',
            ],
            [
                'Llandygái',
                'llandygái',
            ],
            [
                'Caersŵs',
                'caersŵs',
            ],
            [
                'Aberdâr',
                'aberdâr',
            ],
            [
                'Pentredŵr',
                'pentredŵr',
            ],
            [
                'Llannerch-y-môr',
                'llannerch-y-môr',
            ],
            [
                '香港',
                '香港',
            ],
            [
                '東京',
                '東京',
            ],
            [
                'Sydney (C)',
                'sydney-c',
            ],
        ];
    }
}
