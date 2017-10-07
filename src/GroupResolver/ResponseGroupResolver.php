<?php

namespace App\GroupResolver;

use GeoSocio\HttpSerializer\GroupResolver\ResponseGroupResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Response Group Resolver.
 */
class ResponseGroupResolver extends GroupResolver implements ResponseGroupResolverInterface
{

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, $object) : array
    {
        // Prefix the groups so they don't expose something they shouldn't
        // @see https://github.com/symfony/symfony/issues/23494
        return array_map(function ($group) {
            return 'read_' . $group;
        }, $this->getGroups($object));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, $object) : bool
    {
        return true;
    }
}
