<?php

namespace App\GroupResolver;

use App\Entity\User\User;
use GeoSocio\HttpSerializer\GroupResolver\RequestGroupResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Response Group Resolver.
 */
class RequestGroupResolver extends GroupResolver implements RequestGroupResolverInterface
{

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, string $type) : array
    {
        $user = $this->getUser();

        if (!$user) {
            return [User::GROUP_ANONYMOUS];
        }

        return $user->getGroups();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, string $type) : bool
    {
        return true;
    }
}
