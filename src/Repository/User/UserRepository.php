<?php

namespace App\Repository\User;

use App\Entity\User\Email;
use App\Entity\User\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserRepository extends EntityRepository implements UserProviderInterface
{

    /**
     * Create a new User from an Email.
     *
     * @param Email $email Valid email object.
     *
     * @return User Newly created user object.
     */
    public function createFromEmail(Email $email) : User
    {
        $em = $this->getEntityManager();

        // Create a new stub user.
        $user = $this->createStub();

        // Set the Email
        $email->setUser($user);
        $user->setPrimaryEmail($email);

        // Save the Email
        $em->persist($email);
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Create a stub User.
     *
     * @return User Newly created user object.
     */
    private function createStub() : User
    {
        $em = $this->getEntityManager();

        // Create a new stub user.
        $user = new User();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findOneByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
}
