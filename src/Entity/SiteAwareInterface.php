<?php

namespace App\Entity;

interface SiteAwareInterface
{
    /**
     * Get site
     *
     * @return Site
     */
    public function getSite() :? Site;
}
