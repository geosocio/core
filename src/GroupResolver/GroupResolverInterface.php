<?php

namespace App\GroupResolver;

/**
 * Group Resolver.
 */
interface GroupResolverInterface
{
    /**
     * Get the Groups for an object.
     *
     * @param object|null $object
     *
     * @return array
     */
    public function getGroups($object) : array;
}
