<?php

namespace App\Entity\Message;

/**
 * Interface for Messages.
 */
interface MessageInterface
{
    /**
     * Get To.
     */
    public function getTo() : string;

    /**
     * Get SMS message array.
     */
    public function getText() : array;

    /**
     * Get message string.
     */
    public function getTextString() : string;
}
