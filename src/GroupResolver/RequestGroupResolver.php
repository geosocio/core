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
        $groups = [];
        $user = $this->getUser();

        if (!$user) {
            $groups = [
                User::GROUP_ANONYMOUS
            ];
        } else {
            $groups = $user->getGroups();
        }

        // Prefix the groups so they don't expose something they shouldn't
        // @see https://github.com/symfony/symfony/issues/23494
        return array_map(function ($group) {
            return 'write_' . $group;
        }, $groups);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, string $type) : bool
    {
        return true;
    }
}
