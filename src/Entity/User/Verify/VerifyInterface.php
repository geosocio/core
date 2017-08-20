<?php

namespace App\Entity\User\Verify;

/*&
 * Verify Interface
 */
interface VerifyInterface
{
    /**
     * Get Created Date.
     */
    public function getCreated() :? \DateTimeInterface;

    /**
     * Determine if two verifications are equal.
     *
     * @param VerifyInterface $verify
     */
    public function isEqualTo(VerifyInterface $verify) : bool;
}
