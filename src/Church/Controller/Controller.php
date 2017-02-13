<?php

namespace Church\Controller;

use Church\Request\DeserializeRequestTrait;
use Church\Response\SerializerResponseTrait;
use Church\Entity\User\User;
use Church\Response\SerializerResponseInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * An abstract controller to extend.
 */
abstract class Controller implements SerializerResponseInterface
{

    use SerializerResponseTrait;
    use DeserializeRequestTrait;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Creates the Controller.
     *
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param RegistryInterface $doctrine
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RegistryInterface $doctrine,
        TokenStorageInterface $tokenStorage
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Determine if current user is logged in.
     */
    protected function isLoggedIn() : bool
    {
        try {
            $this->getUser();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get a user from the Security Token Storage.
     */
    protected function getUser() : User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            throw new \Exception('Not Logged In.');
        }

        $user = $token->getUser();

        if (!is_object($user)) {
            throw new \Exception('Not Logged In.');
        }

        return $user;
    }

    /**
     * Get the groups for the user.
     *
     * @param array $groups
     */
    protected function getGroups(array $groups = [])
    {
        if ($this->isLoggedIn()) {
            $groups = array_merge($groups, $this->getUser()->getRoles());
        }

        return array_merge($groups, self::DEFAULT_GROUPS);
    }
}
