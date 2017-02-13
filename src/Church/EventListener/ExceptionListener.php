<?php

namespace Church\EventListener;

use Church\Response\SerializerResponseTrait;
use Church\Response\SerializerResponseInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Listen for Exceptions.
 */
class ExceptionListener implements SerializerResponseInterface
{

    use SerializerResponseTrait;

    /**
     * Create the Exception Listener.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
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
                'trace' => $exception->getTrace(),
            ];
        }

        // Override the default request format.
        // @TODO The Symfony exception handler converts the format back to text/html. :(
        if ($request->getRequestFormat() === 'html') {
            $request->setRequestFormat('json');
        }

        if ($exception instanceof HttpExceptionInterface) {
            $response = $this->reply(
                $data,
                $request->getRequestFormat(),
                self::DEFAULT_GROUPS,
                $exception->getStatusCode(),
                $exception->getHeaders()
            );
        } else {
            $response = $this->reply(
                $data,
                $request->getRequestFormat(),
                self::DEFAULT_GROUPS,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Send the modified response object to the event
        $event->setResponse($response);

        return $response;
    }
}
