<?php

namespace GeoSocio\Core\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * Abstract Entity.
 *
 * @MappedSuperclass
 */
abstract class Entity implements EntityInterface
{

    /**
     * Gets a single item from input.
     *
     * @param mixed $data
     * @param string $class
     */
    protected function getSingle($data, string $class)
    {
        if ($data instanceof $class) {
            return $data;
        } elseif (is_array($data)) {
            return new $class($data);
        } else {
            return null;
        }
    }

    /**
     * Gets a single item from input.
     *
     * @param mixed $data
     * @param string $class
     */
    protected function getMultiple($data, string $class) : Collection
    {
        if ($data instanceof Collection) {
            return $data;
        } elseif (is_array($data)) {
            $data = array_map(function ($item) use ($class) {
                return $this->getSingle($item, $class);
            }, $data);
            $data = array_filter($data, function ($item) use ($class) {
                return $item instanceof $class;
            });
            return new ArrayCollection($data);
        } else {
            return new ArrayCollection();
        }
    }
}
