<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * JWT Created Event Listener
 */
class JWTCreatedEventListener
{

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * JWTCreatedEventListener
     *
     * @param NormalizerInterface $normalizer
     */
    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * On JWT Created Event.
     *
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        $data = $this->normalizer->normalize($user, null, [
            'groups' => [ 'read_me' ]
        ]);

        $payload = array_merge($event->getData(), $data);

        $event->setData($payload);
    }
}
