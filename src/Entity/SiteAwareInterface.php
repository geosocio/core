<?php

namespace GeoSocio\Core\Entity;

interface SiteAwareInterface
{
    /**
     * Get site
     *
     * @return Site
     */
    public function getSite() :? Site;
}
