<?php

namespace GeoSocio\Core\Entity\User;

use GeoSocio\Core\Entity\Location;
use GeoSocio\Core\Entity\Entity;
use GeoSocio\Core\Entity\Site;
use GeoSocio\Core\Entity\CreatedTrait;
use GeoSocio\Core\Entity\User\Email;
use GeoSocio\Core\Entity\User\Name;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GeoSocio\Core\Entity\User\User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="GeoSocio\Core\Repository\User\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity({"primaryEmail", "username"})
 */
class User extends Entity implements UserInterface, \Serializable, EquatableInterface, UserAwareInterface
{

    use CreatedTrait;

    /**
     * User Role.
     *
     * Granted to everyone.
     *
     * @var string.
     */
    const ROLE_ANONYMOUS = 'anonymous';

    /**
     * User Role.
     *
     * Granted to all users.
     *
     * @var string.
     */
    const ROLE_AUTHENTICATED = 'authenticated';

    /**
     * User Role.
     *
     * Granted to users with a confirmed email.
     *
     * @var string.
     */
    const ROLE_STANDARD = 'standard';

    /**
     * User Role.
     *
     * Granted to users who are members of the current site.
     *
     * @var string.
     */
    const ROLE_MEMBER = 'member';

    /**
     * User Role.
     *
     * Granted to users who are members of the current site.
     *
     * @var string.
     */
    const ROLE_NEIGHBOR = 'neighbor';

    /**
     * User Role.
     *
     * Granted to one's self.
     *
     * @var string.
     */
    const ROLE_ME = 'me';

    /**
     * @var string
     */
    const OPERATION_READ = 'read';

    /**
     * @var string
     */
    const OPERATION_WRITE = 'write';

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="guid")
     * @ORM\Id
     * @Assert\Uuid
     * @Groups({"anonymous_read"})
     */
    private $id;

    /**
     * @var Name
     *
     * @ORM\Embedded(class = "Name", columnPrefix = "name_")
     * @Assert\Uuid
     * @Groups({"me_read", "neighbor_read"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, unique=true, nullable=true)
     * @Assert\Length(
     *      min = 2,
     *      max = 15
     * )
     * @Assert\Regex(
     *     pattern="/^[a-z\d][a-z\d_]*[a-z\d]$/",
     *     match=true,
     *     message="Username must consist of alphanumeric characters and underscores"
     * )
     * @Groups({"anonymous_read", "me_write"})
     */
    private $username;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="user",  cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * @Groups({"me_read"})
     */
    private $emails;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Membership", mappedBy="user",  cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     * @Groups({"me_read", "standard_read"})
     */
    private $memberships;

    /**
     * @var Email
     *
     * @ORM\OneToOne(targetEntity="Email", mappedBy="email", cascade={"all"})
     * @ORM\JoinColumn(name="primary_email", referencedColumnName="email")
     * @Groups({"me_read"})
     */
    private $primaryEmail;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="GeoSocio\Core\Entity\Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="location_id")
     * @Groups({"me_read", "neighbor_read"})
     */
    private $location;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" = 1})
     * @Groups({"me_read", "me_write"})
     */
    private $enabled;

    /**
     * Create new User.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $id = $data['id'] ?? null;
        $this->id = is_string($id) && uuid_is_valid($id) ? strtolower($id) : strtolower(uuid_create(UUID_TYPE_DEFAULT));

        $name = $data['name'] ?? null;
        $name = $this->getSingle($name, Name::class);
        $this->name = $name ? $name : new Name();

        $username = $data['username'] ?? null;
        $this->username = is_string($username) ? $username : null;

        $emails = $data['emails'] ?? [];
        $this->emails = $this->getMultiple($emails, Email::class);

        $primaryEmail = $data['primaryEmail'] ?? null;
        $this->primaryEmail = $this->getSingle($primaryEmail, Email::class);

        $enabled = $data['enabled'] ?? true;
        $this->enabled = is_bool($enabled) ? $enabled : true;

        $location = $data['location'] ?? null;
        $this->location = $this->getSingle($location, Location::class);

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
    }

    /**
     * @ORM\PostLoad
     */
    public function setNameUser() : self
    {
        $this->name->setUser($this);

        return $this;
    }

    /**
     * Get id
     */
    public function getId() :? string
    {
        return $this->id;
    }

    /**
     * Set Username.
     *
     * @param string $username
     */
    public function setUsername(string $username) : self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUsername() :? string
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getSalt() :? string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getPassword() :? string
    {
        return null;
    }

    /**
     * @inheritDoc
     *
     * @Groups({"me_read"})
     */
    public function getRoles(User $user = null, Site $site = null) : array
    {
        $roles = [
            self::ROLE_ANONYMOUS,
            self::ROLE_AUTHENTICATED,
        ];

        if ($this->primaryEmail
            && $this->primaryEmail->getVerified()
            && $this->name->getFirst()
            && $this->name->getLast()
            && $this->username
            && $this->location
        ) {
            $roles[] = self::ROLE_STANDARD;

            if ($site && $this->isMember($site)) {
                $roles[] = self::ROLE_MEMBER;
            }
        }

        if ($user) {
            if ($this->isEqualTo($user)) {
                $roles[] = self::ROLE_ME;
            }
            if ($this->isNeighbor($user)) {
                $roles[] = self::ROLE_NEIGHBOR;
            }
        }

        return $roles;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials() : void
    {
      // Do something?
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->id
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list (
          $this->id
        ) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(UserInterface $user) : bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if (!$this->id || !$user->getId()) {
            return false;
        }

        if ($this->id !== $user->getId()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the current user and the requested user are in the same
     * place.
     *
     * @param User $user
     */
    public function isNeighbor(User $user) : bool
    {
        if (!$this->location) {
            return false;
        }

        if (!$this->location->getPlace()) {
            return false;
        }

        if (!$user->getLocation()) {
            return false;
        }

        if (!$user->getLocation()->getPlace()) {
            return false;
        }

        if (!in_array(self::ROLE_STANDARD, $this->getRoles())) {
            return false;
        }

        if (!in_array(self::ROLE_STANDARD, $user->getRoles())) {
            return false;
        }

        return $this->location->getPlace()->getId() === $user->getLocation()->getPlace()->getId();
    }

    /**
     * Set Name.
     *
     * @param Name $name
     */
    public function setName(Name $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Name.
     */
    public function getName() :? Name
    {
        return $this->name;
    }

    /**
     * Add emails
     *
     * @param Email $email
     */
    public function addEmail(Email $email) : self
    {
        $this->emails[] = $email;

        return $this;
    }

    /**
     * Remove emails
     *
     * @param Email $email
     */
    public function removeEmail(Email $email) : self
    {
        $this->emails->removeElement($email);

        return $this;
    }

    /**
     * Get emails
     *
     * @return Collection
     */
    public function getEmails() :? Collection
    {
        return $this->emails;
    }

    /**
     * Add Membership
     */
    public function addMembership(Membership $membership) : self
    {
        $this->memberships[] = $membership;

        return $this;
    }

    /**
     * Remove membership
     */
    public function removeMembership(Membership $membership) : self
    {
        $this->memberhsips->removeElement($membership);

        return $this;
    }

    /**
     * Get memberships
     *
     * @return Collection
     */
    public function getMemberships() :? Collection
    {
        return $this->memberships;
    }


    /**
     * Set Primary Email.
     *
     * @param Email $primaryEmail
     * @return User
     */
    public function setPrimaryEmail(Email $primaryEmail) : self
    {
        $this->primaryEmail = $primaryEmail;

        return $this;
    }

    /**
     * Get Primary Email.
     *
     * @return Email
     */
    public function getPrimaryEmail() :? Email
    {
        return $this->primaryEmail;
    }

    /**
     * Set location
     *
     * @param Location $location
     */
    public function setLocation(Location $location) : self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get current location
     */
    public function getLocation() :? Location
    {
        return $this->location;
    }

    /**
     * Set Enabled
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled) : self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get Enabled.
     *
     * @return bool
     */
    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    /**
     * Get Color.
     *
     * @Groups({"anonymous_read"})
     */
    public function getColor() :? string
    {
        return $this->username ? '#' . substr(md5($this->username), 0, 6) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser() :? User
    {
        return $this;
    }

    /**
     * Gets the Groups.
     */
    public static function getGroups(string $operation, array $roles = []) : array
    {
        if (!$roles) {
            $roles = [self::ROLE_ANONYMOUS];
        }

        return array_map(function ($role) use ($operation) {
            return $role . '_' . $operation;
        }, $roles);
    }

    /**
     * Determine if User is a member of a given site.
     */
    public function isMember(Site $site) : bool
    {
        $memberships = $this->memberships->filter(function ($membership) use ($site) {
            return $membership->getSite() && $membership->getSite()->getId() === $site->getId();
        });

        return !$memberships->isEmpty();
    }
}
