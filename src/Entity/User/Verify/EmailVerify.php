<?php

namespace App\Entity\User\Verify;

use App\Entity\User\User;
use App\Entity\User\UserAwareInterface;
use App\Entity\User\Email;
use GeoSocio\EntityUtils\ParameterBag;
use Doctrine\ORM\Mapping as ORM;

/**
 * Email Verify
 *
 * @ORM\Table(name="users_email_verify")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class EmailVerify extends Verify implements UserAwareInterface
{

    /**
     * @var Email
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="\App\Entity\User\Email", inversedBy="verify")
     * @ORM\JoinColumn(name="email", referencedColumnName="email")
     */
    private $email;

    /**
     * Create new Email Verify.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->email = $params->getInstance('email', Email::class);

        parent::__construct($data);
    }

    /**
     * Set email
     *
     * @param Email $email
     */
    public function setEmail(Email $email) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     */
    public function getEmail() :? Email
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser() :? User
    {
        if ($this->email) {
            return $this->email->getUser();
        }

        return null;
    }
}
