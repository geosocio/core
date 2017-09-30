<?php

namespace App\GroupResolver;

use App\Entity\User\User;
use App\Entity\SiteAwareInterface;
use App\Entity\User\UserAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Group Resolver.
 */
class GroupResolver implements GroupResolverInterface
{

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Create Response Group Resolver.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups($object) : array
    {
        $user = $this->getUser();

        if (!$user) {
            return [User::GROUP_ANONYMOUS];
        }

        $groups = $user->getGroups();

        if ($object instanceof SiteAwareInterface && $user->isStandard()) {
            if ($user->isMember($object->getSite())) {
                $groups[] = User::GROUP_MEMBER;
            }
        }

        if ($object instanceof UserAwareInterface) {
            if ($user->isEqualTo($object->getUser())) {
                $groups[] = User::GROUP_ME;
            }
            if ($user->isNeighbor($object->getUser())) {
                $groups[] = User::GROUP_NEIGHBOR;
            }
        }

        return $groups;
    }

    /**
     * Get the logged in user.
     *
     * @return User|null
     */
    protected function getUser() :? User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        return $user;
    }
}
