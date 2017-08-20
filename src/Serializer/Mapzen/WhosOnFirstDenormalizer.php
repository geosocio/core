<?php

namespace App\Serializer\Mapzen;

use App\Entity\Place\Place;
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
        if (isset($data['place']['wof:parent_id']) && $data['place']['wof:parent_id'] != '-1') {
            $parent = [
                'id' => (int) $data['place']['wof:parent_id'],
            ];
        }
        return new $class([
            'id' => $data['place']['wof:id'] ?? null,
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
        if (!empty($data['place']['wof:lang_x_official'])) {
            $langs = $data['place']['wof:lang_x_official'];
        } elseif (!empty($data['place']['wof:lang'])) {
            $langs = $data['place']['wof:lang'];
        }
        foreach ($langs as $lang) {
            if (!empty($data['place']['name:' . $lang . '_x_preferred'])) {
                return $data['place']['name:' . $lang . '_x_preferred'][0];
            }
        }

        return $data['place']['wof:name'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return ($type === Place::class || is_subclass_of($type, Place::class)) && array_key_exists('place', $data);
    }
}
