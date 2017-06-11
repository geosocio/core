<?php

namespace GeoSocio\Core\Entity;

use GeoSocio\Core\Entity\User\User;

// @TODO Replace with Symfony Security Voter.
//       @see http://symfony.com/doc/current/security/voters.html
interface AccessAwareInterface
{
    /**
     * Can View.
     */
    public function canView(User $user = null) : bool;

    /**
     * Can Edit.
     */
    public function canEdit(User $user = null) : bool;

    /**
     * Can Create.
     */
    public function canCreate(User $user = null) : bool;

    /**
     * Can Delete.
     */
    public function canDelete(User $user = null) : bool;

    /**
     * Get a placeholder object when user does not have access.
     */
    public function getPlaceholder();
}
