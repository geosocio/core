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
        return $this->getGroups($object);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, $object) : bool
    {
        return true;
    }
}
