<?php

namespace GeoSocio\Core\EventListener;

use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\UserAwareInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;

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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * Creates the Event Listener.
     */
    public function __construct(
        SerializerInterface $serializer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->serializer = $serializer;
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

        $user = null;
        if ($result instanceof UserAwareInterface) {
            $user = $result->getUser();
        }

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

        $roles = $this->getUser() ? $this->getUser()->getRoles($user) : [];

        $response = $this->getResponse($result, $request->getRequestFormat('json'), $roles, $status);
        $event->setResponse($response);

        return $response;
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
