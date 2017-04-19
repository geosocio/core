<?php

namespace GeoSocio\Core\Serializer\Mapzen;

use GeoSocio\Core\Entity\Place\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalizes the Search Response.
 */
class WhosOnFirstDenormalizer implements DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $parent = null;
        if (isset($data['record']['wof:parent_id']) && $data['record']['wof:parent_id'] != '-1') {
            $parent = [
                'id' => (int) $data['record']['wof:parent_id'],
            ];
        }
        return new $class([
            'id' => $data['record']['wof:id'] ?? null,
            'parent' => $parent,
            'name' => $this->getName($data),
        ]);
    }

    /**
     * Get the names from the data.
     *
     * @param array $data
     */
    protected function getName(array $data) : string
    {
        $langs = [];
        if (!empty($data['record']['wof:lang_x_official'])) {
            $langs = $data['record']['wof:lang_x_official'];
        } elseif (!empty($data['record']['wof:lang'])) {
            $langs = $data['record']['wof:lang'];
        }
        foreach ($langs as $lang) {
            if (!empty($data['record']['name:' . $lang . '_x_preferred'])) {
                return $data['record']['name:' . $lang . '_x_preferred'][0];
            }
        }

        return $data['record']['wof:name'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return ($type === Place::class || is_subclass_of($type, Place::class)) && array_key_exists('record', $data);
    }
}
