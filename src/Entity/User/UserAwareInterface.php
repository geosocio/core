<?php

namespace GeoSocio\Core\Entity\User;

interface UserAwareInterface
{
    /**
     * Get user
     */
    public function getUser() :? User;
}
