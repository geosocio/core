<?php

namespace App\Utils\User;

use App\Entity\User\Verify\VerifyInterface;

/**
 * Verification Interface.
 */
interface VerificationInterface
{

    /**
     * Creates a new verification.
     *
     * @param string $item
     */
    public function create(string $item) : VerifyInterface;

    /**
     * Sends a verification by the appropriate method.
     *
     * @param VerifyInterface $item
     */
    public function send(VerifyInterface $item) : bool;
}
