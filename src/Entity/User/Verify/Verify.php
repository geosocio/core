<?php

namespace GeoSocio\Core\Entity\User\Verify;

use GeoSocio\Core\Entity\Entity;
use Doctrine\ORM\Mapping as ORM;

use GeoSocio\Core\Entity\CreatedTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Abstract Verification.
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class Verify extends Entity implements VerifyInterface
{

    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=6, unique=true)
     * @Assert\NotBlank()
     */
    private $token;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $hashedCode;

    /**
     * Create new Email Verify.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $token = $data['token'] ?? null;
        $this->token = is_string($token) ? $token : null;

        $code = $data['code'] ?? null;
        $this->code = is_string($code) ? $code : null;

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
    }

    /**
     * @ORM\PrePersist
     */
    public function hashData() : self
    {
        if ($this->code) {
            $this->hashedCode = $this->hash($this->code);
        }

        return $this;
    }

    /**
     * Hash data.
     *
     * @param string $data
     */
    protected function hash(string $data) : string
    {
        return password_hash($data, PASSWORD_DEFAULT);
    }

    /**
     * Set token
     *
     * @Groups({"anonymous"})
     */
    public function setToken(string $token) : self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @Groups({"anonymous"})
     */
    public function getToken() :? string
    {
        return $this->token;
    }

    /**
     * Set code
     *
     * @Groups({"anonymous"})
     */
    public function setCode(string $code) : self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     */
    public function getCode() :? string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(VerifyInterface $verify) : bool
    {

        if (!$this->token || !$verify->getToken()) {
            return false;
        }

        if ($this->token !== $verify->getToken()) {
            return false;
        }

        if (!$this->hashedCode || !$verify->getCode()) {
            return false;
        }

        if (!password_verify($verify->getCode(), $this->hashedCode)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if verification is fresh.
     */
    public function isFresh()
    {
        if (!$this->created) {
            throw new \LogicException("Missing Created");
        }

        $created = clone $this->created;
        // It might be neccesary to inject the \DateInterval in the future.
        $created->add(new \DateInterval('PT1H'));

        $now = new \DateTime('now');

        if ($created < $now) {
            return false;
        }

        return true;
    }
}
