<?php

namespace GeoSocio\Core\Entity\User;

use GeoSocio\Core\Entity\EntityInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Login Entity.
 *
 * This entity is not persisted into the database.
 */
class Login implements EntityInterface
{

    /**
     * @var string
     *
     * @Assert\Length(
     *      max = "255"
     * )
     * @Assert\Email(
     *     strict = true,
     *     checkMX = true
     * )
     */
    protected $value;

    /**
     * Creates a new Login.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $value = $data['value'] ?? '';
        $this->value = is_string($value) ? $value : '';
    }

    /**
     * Sets the value of the login.
     *
     * @Groups({"anonymous"})
     */
    public function setValue(string $value) : self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns the value of the login.
     */
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * Returns the type of login.
     */
    public function getType() : string
    {
        return 'email';
    }

    /**
     * Convers the login object to a string.
     */
    public function __toString() : string
    {
        return $this->value;
    }
}
