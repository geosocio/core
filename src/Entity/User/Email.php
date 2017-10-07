<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityUtils\ParameterBag;
use GeoSocio\EntityUtils\CreatedTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

use App\Entity\User\User;
use App\Entity\User\Verify\EmailVerify;

/**
 * App\Entity\User\Email
 *
 * @ORM\Table(name="users_email")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Email implements UserAwareInterface
{
    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(
     *     strict = true,
     *     checkMX = true
     * )
     */
    private $email;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="emails")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * @var EmailVerify
     *
     * @ORM\OneToOne(
     *  targetEntity="\App\Entity\User\Verify\EmailVerify",
     *  mappedBy="email",
     *  cascade={"remove"}
     * )
     */
    private $verify;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $verified;

    /**
     * Create new Email.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->email = $params->getString('email');
        $this->user = $params->getInstance('user', User::class);
        $this->created = $params->getInstance('created', \DateTimeInterface::class);
        $this->verify = $params->getInstance('verify', EmailVerify::class);
        $this->verified = $params->getInstance('verified', \DateTimeInterface::class);
    }

    /**
     * Set email
     *
     * @Groups({"write_me"})
     *
     * @param string $email
     * @return Email
     */
    public function setEmail(string $email) : self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @Groups({"read_me"})
     *
     * @return string
     */
    public function getEmail() :? string
    {
        return $this->email;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Email
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

    /**
     * Set verified
     *
     * @param \DateTime $verified
     * @return Email
     */
    public function setVerified(\DateTime $verified) : self
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Get verified.
     *
     * @Groups({"read_me"})
     */
    public function getVerified() :? \DateTimeInterface
    {
        return $this->verified;
    }

    /**
     * Set verify
     *
     * @param EmailVerify $verify
     */
    public function setVerify(EmailVerify $verify) : self
    {
        $this->verify = $verify;

        return $this;
    }

    /**
     * Get verify
     */
    public function getVerify() :? EmailVerify
    {
        return $this->verify;
    }

    /**
     * Convers the email object to a string.
     */
    public function __toString() : string
    {
        return $this->email ?: '';
    }

    /**
     * Determines if one email is equal to another.
     *
     * @param Email $email
     */
    public function isEqualTo(Email $email) : bool
    {
        if ($this->email !== $email->getEmail()) {
            return false;
        }

        return true;
    }
}
