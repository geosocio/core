<?php

namespace GeoSocio\Core\Entity\User;

use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\EntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines a person's name.
 *
 * @ORM\Embeddable()
 */
class Name implements EntityInterface, UserAwareInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="first", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255
     * )
     */
    private $first;

    /**
     * @var string
     *
     * @ORM\Column(name="last", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255
     * )
     */
    private $last;

    /**
     * @var User
     */
    private $user;

    /**
     * Create new User.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $first = $data['first'] ?? null;
        $this->first = is_string($first) ? $first : null;

        $last = $data['last'] ?? null;
        $this->last = is_string($last) ? $last : null;

        $user = $data['user'] ?? null;
        $this->user = $user instanceof User ? $user : null;
    }

    /**
     * Set First Name.
     *
     * @param string $first
     */
    public function setFirst(string $first) : self
    {
        $this->first = $first;

        return $this;
    }

    /**
     * Get First Name.
     */
    public function getFirst() :? string
    {
        return $this->first;
    }

    /**
     * Set Last.
     *
     * @param string $last
     */
    public function setLast(string $last) : self
    {
        $this->last = $last;

        return $this;
    }

    /**
     * Get Last Name.
     *
     * @return string
     */
    public function getLast() :? string
    {
        return $this->last;
    }

    /**
     * Set the User.
     */
    public function setUser(User $user) : self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser() :? User
    {
        return $this->user;
    }
}
