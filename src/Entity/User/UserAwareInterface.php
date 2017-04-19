<?php

namespace GeoSocio\Core\Entity\User;

interface UserAwareInterface
{
    /**
     * Get user
     *
     * @return User
     */
    public function getUser() :? User;
}
