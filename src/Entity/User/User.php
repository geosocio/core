<?php

namespace App\Entity\User;

use App\Entity\Place\Place;
use Doctrine\Common\Collections\Criteria;
use App\Entity\Location;
use App\Entity\Site;
use App\Entity\User\Email;
use App\Entity\User\Name;
use App\Entity\User\Membership;
use Doctrine\ORM\Mapping as ORM;
use GeoSocio\EntityUtils\ParameterBag;
use GeoSocio\EntityUtils\CreatedTrait;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * App\Entity\User\User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\User\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity({"primaryEmail", "username"})
 */
class User implements UserInterface, \Serializable, EquatableInterface, UserAwareInterface
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
     *
     * @ORM\Column(name="user_id", type="guid")
     * @ORM\Id
     * @Assert\Uuid
     */
    private $id;

    /**
     * @var Name
     *
     * @ORM\Embedded(class = "Name", columnPrefix = "name_")
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
     */
    private $username;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Email", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $emails;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Membership", mappedBy="user")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $memberships;

    /**
     * @var Email
     *
     * @ORM\OneToOne(targetEntity="Email", mappedBy="email")
     * @ORM\JoinColumn(name="primary_email", referencedColumnName="email")
     */
    private $primaryEmail;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(name="location", referencedColumnName="location_id")
     */
    private $location;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $disabled;

    /**
     * Create new User.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->id = $params->getUuid('id', strtolower(uuid_create(UUID_TYPE_DEFAULT)));
        $this->name = $params->getInstance('name', Name::class, new Name());
        $this->username = $params->getString('username');
        $this->emails = $params->getCollection('emails', Email::class, new ArrayCollection());
        $this->primaryEmail = $params->getInstance('primaryEmail', Email::class);
        $this->location = $params->getInstance('location', Location::class);
        $this->memberships = $params->getCollection('memberships', Membership::class, new ArrayCollection());
        $this->disabled = $params->getInstance('disabled', \DateTimeInterface::class);
        $this->disabled = $params->getInstance('created', \DateTimeInterface::class);
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
     * Set id
     *
     * @param string $id
     */
    public function setId(string $id) : self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @Groups({"anonymous"})
     */
    public function getId() :? string
    {
        return $this->id;
    }

    /**
     * Set Username.
     *
     * @Groups({"me"})
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
     *
     * @Groups({"anonymous"})
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
     * @TODO Roles must start with `ROLE_` and be uppercase!
     *
     * @Groups({"me"})
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
     * @Groups({"neighbor", "me"})
     */
    public function getFirstName() :? string
    {
        if ($this->name) {
            return $this->name->getFirst();
        }

        return null;
    }

    /**
     * Set First Name
     *
     * @param string $firstName
     *
     * @Groups({"me"})
     */
    public function setFirstName(string $firstName) : self
    {
        if ($this->name) {
            $this->name->setFirst($firstName);
        } else {
            $this->name = new Name([
                "first" => $firstName,
            ]);
        }

        return $this;
    }

    /**
     * @Groups({"neighbor", "me"})
     */
    public function getLastName() :? string
    {
        if ($this->name) {
            return $this->name->getLast();
        }

        return null;
    }

    /**
     * Set Last Name
     *
     * @param string $lastName
     *
     * @Groups({"me"})
     */
    public function setLastName(string $lastName) : self
    {
        if ($this->name) {
            $this->name->setLast($lastName);
        } else {
            $this->name = new Name([
                "last" => $lastName,
            ]);
        }

        return $this;
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
     * @Groups({"me"})
     *
     * @return Collection
     */
    public function getEmails() :? Collection
    {
        return $this->emails;
    }

    /**
     * Add Membership
     *
     * @param Membership $membership
     */
    public function addMembership(Membership $membership) : self
    {
        $this->memberships[] = $membership;

        return $this;
    }

    /**
     * Remove membership
     *
     * @param Membership $membership
     */
    public function removeMembership(Membership $membership) : self
    {
        $this->memberhsips->removeElement($membership);

        return $this;
    }

    /**
     * Get memberships
     *
     * @Groups({"me", "standard"})
     *
     * @return Collection
     */
    public function getMemberships() : Collection
    {
        return $this->memberships;
    }

    /**
     * Get Memberships by Site.
     *
     * @param Site $site
     */
    public function getMembershipsBySite(Site $site) : Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("site", $site));
        return $this->memberships->matching($criteria);
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
     * Set Primary Email.
     *
     * @param string $primaryEmailAddress
     *
     * @return User
     *
     * @Groups({"me"})
     */
    public function setPrimaryEmailAddress(string $primaryEmailAddress) : self
    {
        // Always override the entire primaryEmail object
        // rather than modifying the id.
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq("email", $primaryEmailAddress));
        $emails = $this->emails->matching($criteria);

        if ($primary = $emails->first()) {
            $this->primaryEmail = $primary;
        } else {
            $this->primaryEmail = new Email([
                'email' => $primaryEmailAddress,
            ]);
        }

        return $this;
    }

    /**
     * Get Primary Email.
     *
     * @Groups({"me"})
     *
     * @return string
     */
    public function getPrimaryEmailAddress() :? string
    {
        return $this->primaryEmail ?: '';
    }

    /**
     * Set location
     *
     * @param Location|null $location
     */
    public function setLocation(?Location $location) : self
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
     * Get current location id.
     *
     * @Groups({"me"})
     */
    public function getLocationId() :? string
    {
        if ($this->location) {
            return $this->location->getId();
        }

        return null;
    }

    /**
     * Get current location id.
     *
     * @param string $id
     *
     * @Groups({"me"})
     */
    public function setLocationId(string $id) : self
    {
        // Always override the entire locaiton object rather than modifying the
        // id.
        $this->location = new Location([
            'id' => $id,
        ]);

        return $this;
    }

    /**
     * Get current place id.
     *
     * @Groups({"me", "neighbor"})
     */
    public function getPlaceId() :? int
    {
        if ($this->location) {
            return $this->location->getPlaceId();
        }

        return null;
    }

    /**
     * Mark user as disabled.
     */
    public function disable() : self
    {
        $this->disabled = new \DateTime();

        return $this;
    }

    /**
     * Mark user as enabled.
     */
    public function enable() : self
    {
        $this->disabled = null;

        return $this;
    }

    /**
     * Get Enabled.
     *
     * @Groups({"me"})
     */
    public function isEnabled() : bool
    {
        return !$this->disabled;
    }

    /**
     * Get Color.
     *
     * @Groups({"anonymous"})
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
     * Determine if User is a member of a given site.
     *
     * @param Site $site
     */
    public function isMember(Site $site) : bool
    {
        return !$this->getMembershipsBySite($site)->isEmpty();
    }

    /**
     * Get a user's place
     */
    public function getPlace() :? Place
    {
        if (!$this->location) {
            return null;
        }

        return $this->location->getPlace();
    }

    /**
     * Get a user's places.
     */
    public function getPlaces() : Collection
    {
        if (!$this->location) {
            return new ArrayCollection();
        }

        return $this->location->getPlaces();
    }
}
