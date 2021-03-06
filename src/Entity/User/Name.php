<?php

namespace App\Entity\User;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityUtils\ParameterBag;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines a person's name.
 *
 * @ORM\Embeddable()
 */
class Name implements UserAwareInterface
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
        $params = new ParameterBag($data);
        $this->first = $params->getString('first');
        $this->last = $params->getString('last');
        $this->user = $params->getInstance('user', User::class);
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
     *
     * @param User $user
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
