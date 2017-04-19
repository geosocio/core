<?php

namespace GeoSocio\Core\ArgumentResolver;

use GeoSocio\Core\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthenticatedUserResolver implements ArgumentValueResolverInterface
{

    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument) : bool
    {
        if (User::class !== $argument->getType()) {
            return false;
        }

        if ($argument->getName() !== 'authenticated') {
            return false;
        }

        $token = $this->tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            return false;
        }

        return $token->getUser() instanceof User;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument) : \Generator
    {
        yield $this->tokenStorage->getToken()->getUser();
    }
}
