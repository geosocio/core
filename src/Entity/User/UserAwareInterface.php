<?php

namespace App\Entity\User;

interface UserAwareInterface
{
    /**
     * Get user
     */
    public function getUser() :? User;
}
