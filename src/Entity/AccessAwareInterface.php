<?php

namespace GeoSocio\Core\Entity;

use GeoSocio\Core\Entity\User\User;

interface AccessAwareInterface
{
    public function canView(User $user = null) : bool;

    public function canEdit(User $user = null) : bool;

    public function canCreate(User $user = null) : bool;

    public function canDelete(User $user = null) : bool;
}
