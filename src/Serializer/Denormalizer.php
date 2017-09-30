<?php

namespace App\Serializer;

use App\GroupResolver\GroupResolverInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
     * @var GroupResolverInterface
     */
    protected $groupResolver;

    /**
     * Creates the Controller.
     *
     * @param DenormalizerInterface $denormalizer
     * @param ValidatorInterface $validator
     * @param GroupResolverInterface $groupResolver
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        GroupResolverInterface $groupResolver
    ) {
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
        $this->groupResolver = $groupResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!is_string($class) && !is_object($class)) {
            throw new \InvalidArgumentException('type must be a string or an object');
        }

        $object = null;
        if (is_object($class)) {
            $object = $class;
            $class = get_class($object);
        }

        $context = array_merge([
            'object_to_populate' => $object,
            'groups' => $this->groupResolver->getGroups($object),
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
