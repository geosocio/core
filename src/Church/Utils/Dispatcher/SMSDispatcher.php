<?php

namespace Church\Utils\Dispatcher;

use NexmoMessage as Nexmo;
use Church\Entity\Message\MessageInterface;
use Church\Message\SMS as Message;

class SMSDispatcher implements DispatcherInterface
{

    protected $nexmo;

    protected $from;

    /**
     * Send an SMS message.
     *
     * @param Nexmo $nexmo
     *    Nexmo Object from SDK.
     * @param string $from
     *    SMS From Number.
     */
    public function __construct(Nexmo $nexmo, $from)
    {
        $this->nexmo = $nexmo;
        $this->from = $from;
    }

    /**
     * Send an SMS message.
     *
     * @param Message
     *    Message Object compatible this object.
     */
    public function send(MessageInterface $message) : bool
    {

        // Send the Message.
        $result = $this->nexmo->sendText(
            $message->getTo(),
            $this->from,
            $message->getTextString()
        );

        // Nexmo does not throw an exception when there was an error, so we'll
        // throw one ourselves.
        if (!empty($result->messages)) {
            $error = $result->messages[0];
            if (!empty($error->errortext)) {
                throw new \Exception($error->errortext);
            }
        }

        return true;
    }
}