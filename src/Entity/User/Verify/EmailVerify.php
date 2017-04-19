<?php

namespace GeoSocio\Core\Entity\User\Verify;

use Doctrine\ORM\Mapping as ORM;

use GeoSocio\Core\Entity\User\User;
use GeoSocio\Core\Entity\User\UserAwareInterface;
use GeoSocio\Core\Entity\User\Email;

/**
 * GeoSocio\Core\Entity\User\EmailVerify
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
     * @ORM\OneToOne(targetEntity="\GeoSocio\Core\Entity\User\Email", inversedBy="verify")
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
        $email = $data['email'] ?? null;
        $this->email = $this->getSingle($email, Email::class);

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
