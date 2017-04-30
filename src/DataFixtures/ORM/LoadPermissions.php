<?php

namespace GeoSocio\Core\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use GeoSocio\Core\Entity\Permission;

class LoadPermissions implements FixtureInterface
{
    protected const PERMISSIONS = [
        [
            'id' => 'public',
            'name' => 'Public',
        ],
        [
            'id' => 'site',
            'name' => 'Site',
        ],
        [
            'id' => 'place',
            'name' => 'Place',
        ],
        [
            'id' => 'me',
            'name' => 'Me',
        ],
    ];

    public function load(ObjectManager $em)
    {
        $permissions = array_map(function ($data) use ($em) {
            $permission = new Permission($data);
            $em->persist($permission);
            return $permission;
        }, self::PERMISSIONS);

        $em->flush();

        return $permissions;
    }
}
