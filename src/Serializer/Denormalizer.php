<?php

namespace GeoSocio\Core\Serializer;

use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\UserAwareInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Deserializes a request body with validaiton.
 */
class Denormalizer implements DenormalizerInterface
{

    /**
     * @var DenormalizerInterface
     */
    protected $denormalizer;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Creates the Controller.
     *
     * @param ValidatorInterface $validator
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get a user from the Security Token Storage.
     */
    protected function getUser() :? User
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();

        if (!is_object($user)) {
            return null;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!is_string($class) && !is_object($class)) {
            throw new \InvalidArgumentException('type must be a string or an object');
        }

        $roles = $context['roles'] ?? [];

        $object = null;
        if (is_object($class)) {
            $object = $class;
            $class = get_class($object);
        }

        if ($object instanceof UserAwareInterface) {
            $user = $object->getUser();
        }

        $roles = $this->getUser() ? $this->getUser()->getRoles($user) : [];

        $context = array_merge([
            'object_to_populate' => $object,
            'groups' => User::getGroups(User::OPERATION_WRITE, $roles),
        ], $context);

        $result = $this->denormalizer->denormalize(
            $data,
            $class,
            $format,
            $context
        );

        $this->validate($result);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }

    /**
     * Validate an object.
     *
     * @param object $data
     */
    protected function validate($data) : bool
    {
        $errors = $this->validator->validate($data);

        if (count($errors)) {
            throw new BadRequestHttpException((string) $errors);
        }

        return true;
    }
}
