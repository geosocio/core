<?php

namespace App\Security;

use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    /**
     * @var string
     */
    const VIEW = 'view';

    /**
     * @var string
     */
     const EDIT = 'edit';

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser() instanceof User ? $token->getUser() : null;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    /**
     * Determine if User can be viewed.
     *
     * @param User $subject
     * @param User|null $user
     *
     * @return bool
     */
    protected function canView(User $subject, ?User $user) : bool
    {
        // Always allow a user to view their own account.
        if ($user && $subject->isEqualTo($user)) {
            return true;
        }

        if (!$subject->isEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if User can be edited.
     *
     * @param User $subject
     * @param User|null $user
     */
    protected function canEdit(User $subject, ?User $user) : bool
    {
        // If you cannot view the user, you certainly cannot edit it.
        if (!$this->canView($subject, $user)) {
            return false;
        }

        // Must be logged in to edit.
        if (!$user) {
            return false;
        }

        // Only allow editing your own user.
        if (!$subject->isEqualTo($user)) {
            return false;
        }

        return true;
    }
}
