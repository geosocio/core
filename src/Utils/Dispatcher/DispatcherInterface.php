<?php

namespace App\Utils\Dispatcher;

use App\Entity\Message\MessageInterface;

/**
 * Dispatcher Interface
 */
interface DispatcherInterface
{

    /**
     * Send an Email message.
     *
     * @param MessageInterface $message
     */
    public function send(MessageInterface $message) : bool;
}
