<?php

namespace App\Security;

use App\Entity\Post\Post;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostVoter extends Voter
{
    /**
     * @var string
     */
    const VIEW = 'view';

    /**
     * @var string
     */
    const CREATE = 'create';

    /**
     * @var string
     */
    const EDIT = 'edit';

    /**
     * @var string
     */
     const DELETE = 'delete';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $post, TokenInterface $token)
    {
        $user = $token->getUser() instanceof User ? $token->getUser() : null;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($post, $user);
            case self::CREATE:
                return $this->canCreate($post, $user);
            case self::EDIT:
                return $this->canEdit($post, $user);
            case self::DELETE:
                return $this->canDelete($post, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Determine if Post can be viewed.
     *
     * @param Post $post
     * @param User|null $user
     *
     * @return bool
     */
    protected function canView(Post $post, ?User $user) : bool
    {
        if ($post->isDeleted()) {
            return false;
        }

        if (!$post->getPermission()) {
            return false;
        }

        if ($post->getPermissionId() === 'public') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($post->getPermissionId() === 'me' && !$post->getUser()->isEqualTo($user)) {
            return false;
        }

        if ($post->getPermissionId() === 'site' && !$user->isMember($post->getSite())) {
            return false;
        }

        if ($post->getPermissionId() === 'place') {
            if (!$post->getPermissionPlace() || !$user->getPlaces()->contains($this->getPermissionPlace())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if Post can be created.
     *
     * @param Post $post
     * @param User|null $user
     */
    protected function canCreate(Post $post, ?User $user) : bool
    {
        if (!$this->canView($user)) {
            return false;
        }

        if (!$post->getUser() || !$user) {
            return false;
        }

        if (!$post->getUser()->isEqualTo($user)) {
            return false;
        }

        if ($post->getSite() && !$user->isMember($post->getSite())) {
            return false;
        }

        return true;
    }

    /**
     * Determine if Post can be edited.
     *
     * @param Post $post
     * @param User|null $user
     */
    protected function canEdit(Post $post, ?User $user) : bool
    {
        return false;
    }

    /**
     * Determine if Post can be created.
     *
     * @param Post $post
     * @param User|null $user
     */
    protected function canDelete(Post $post, ?User $user) : bool
    {
        if (!$this->canView($post, $user)) {
            return false;
        }

        if (!$post->getUser() || !$user) {
            return false;
        }

        if (!$this->getUser()->isEqualTo($user)) {
            return false;
        }

        return true;
    }
}
