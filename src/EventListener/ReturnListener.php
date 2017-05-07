<?php

namespace GeoSocio\Core\EventListener;

use Doctrine\Common\Collections\Collection;
use GeoSocio\Core\Entity\SiteAwareInterface;
use GeoSocio\Core\Entity\AccessAwareInterface;
use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\UserAwareInterface;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Listen for Exceptions.
 */
class ReturnListener
{

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Creates the Event Listener.
     */
    public function __construct(
        SerializerInterface $serializer,
        NormalizerInterface $normalizer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
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
     * Generates the Response.
     */
    protected function getResponse(
        $data,
        string $format,
        array $roles = [],
        int $status = 200,
        array $headers = []
    ) : Response {

        $context = [
            'groups' => User::getGroups(User::OPERATION_READ, $roles),
            'enable_max_depth' => true,
        ];
        return new Response(
            $this->serializer->serialize($data, $format, $context),
            $status,
            $headers
        );
    }

    /**
     * Handle the Kernel Exception.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event) : Response
    {
        $request = $event->getRequest();
        $result = $event->getControllerResult();

        $status = 200;
        switch ($request->getMethod()) {
            case 'POST':
                $status = 201;
                break;
            case 'DELETE':
                $status = 204;
                break;
            default:
                $status = 200;
                break;
        }

        if (is_iterable($result)) {
            if ($result instanceof Collection) {
                $result = $result->toArray();
            }

            $result = array_map(function ($item) {
                // Check to make sure the user has access to each item in the
                // collection.
                if ($item instanceof AccessAwareInterface && !$item->canView($this->getUser())) {
                    $item = [
                        // @TODO This needs an interface.
                        'id' => $item->getId(),
                        'deleted' => true,
                    ];
                }

                return $this->normalize($item);
            }, $result);
        } else {
            if ($result instanceof AccessAwareInterface && !$result->canView($this->getUser())) {
                throw new AccessDeniedException();
            }

            $result = $this->normalize($result);
        }

        $response = $this->getResponse($result, $request->getRequestFormat('json'), [], $status);
        $event->setResponse($response);

        return $response;
    }

    /**
     * Normalize an object.
     */
    protected function normalize($result)
    {
        $user = null;
        if ($result instanceof UserAwareInterface) {
            $user = $result->getUser();
        }

        $site = null;
        if ($result instanceof SiteAwareInterface) {
            $site = $result->getSite();
        }

        $roles = $this->getUser() ? $this->getUser()->getRoles($user, $site) : [];

        $context = [
            'groups' => User::getGroups(User::OPERATION_READ, $roles),
            'enable_max_depth' => true,
        ];

        return $this->normalizer->normalize($result, null, $context);
    }

    /**
     * Handle the Kernel Exception.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event) : Response
    {
        // You get the exception object from the received event
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($exception instanceof HttpExceptionInterface) {
            $data = [
                'error' => $exception->getMessage(),
            ];
        } else {
            $data = [
                'error' => $exception->getMessage(),
                'type' => get_class($exception),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        // Override the default request format.
        // @TODO The Symfony exception handler converts the format back to text/html. :(
        if ($request->getRequestFormat('json') === 'html') {
            $request->setRequestFormat('json');
        }

        if ($exception instanceof HttpExceptionInterface) {
            $response = $this->getResponse(
                $data,
                $request->getRequestFormat(),
                [],
                $exception->getStatusCode(),
                $exception->getHeaders()
            );
        } else {
            $response = $this->getResponse(
                $data,
                $request->getRequestFormat(),
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Send the modified response object to the event
        $event->setResponse($response);

        return $response;
    }
}
