<?php

namespace GeoSocio\Core\Entity\Place;

use GeoSocio\Core\Entity\Location;
use GeoSocio\Core\Entity\Entity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use GeoSocio\Core\Entity\TreeAwareInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * GeoSocio\Core\Entity\Place
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="place")
 * @ORM\Entity()
 */
class Place extends Entity implements TreeAwareInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="place_id", type="integer")
     * @ORM\Id
     * @Groups({"anonymous_read"})
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true, length=255)
     * @Groups({"anonymous_read"})
     */
    private $slug;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="Place")
     * @ORM\JoinColumn(name="parent", referencedColumnName="place_id")
     */
    private $parent;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Tree", mappedBy="descendant")
     * @ORM\JoinColumn(name="place_id", referencedColumnName="descendant")
     */
    private $ancestor;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Tree", mappedBy="ancestor")
     * @ORM\JoinColumn(name="place_id", referencedColumnName="ancestor")
     */
    private $descendant;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="GeoSocio\Core\Entity\Location", mappedBy="place",  cascade={"all"})
     * @ORM\JoinColumn(name="place_id", referencedColumnName="place_id")
     */
    private $locations;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * Create new Place.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $id = $data['id'] ?? null;
        $this->id = is_integer($id) ? $id : null;

        $name = $data['name'] ?? '';
        $this->name = is_string($name) ? $name : null;

        $slug = $data['slug'] ?? null;
        $this->slug = is_string($slug) ? $slug : null;

        $parent = $data['parent'] ?? null;
        $this->parent = $this->getSingle($parent, Place::class);

        $ancestor = $data['ancestor'] ?? null;
        $this->ancestor = $this->getMultiple($ancestor, Tree::class);

        $descendant = $data['descendant'] ?? null;
        $this->descendant = $this->getMultiple($descendant, Tree::class);

        $locations = $data['locations'] ?? null;
        $this->locations = $this->getMultiple($locations, Location::class);

        $created = $data['created'] ?? null;
        $this->created = $created instanceof \DateTimeInterface ? $created : null;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = new \DateTime();
    }

    /**
     * Get id
     */
    public function getId() :? int
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     */
    public function setId(int $id) : self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set Name.
     *
     * @param string $name
     */
    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Name.
     */
    public function getName() :? string
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug(string $slug) : self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     */
    public function getSlug() :? string
    {
        return $this->slug;
    }

    /**
     * Add ancestor
     *
     * @param Tree $ancestor
     */
    public function addAncestor(Tree $ancestor) : self
    {
        $this->ancestor[] = $ancestor;

        return $this;
    }

    /**
     * Remove ancestor
     *
     * @param Tree $ancestor
     */
    public function removeAncestor(Tree $ancestor) : self
    {
        $this->ancestor->removeElement($ancestor);

        return $this;
    }

    /**
     * Get ancestor
     */
    public function getAncestor() : Collection
    {
        return $this->ancestor;
    }

    /**
     * Add descendant
     *
     * @param Tree $descendant
     */
    public function addDescendant(Tree $descendant) : self
    {
        $this->descendant[] = $descendant;

        return $this;
    }

    /**
     * Remove descendant
     *
     * @param Tree $descendant
     */
    public function removeDescendant(Tree $descendant) : self
    {
        $this->descendant->removeElement($descendant);
    }

    /**
     * Get descendant
     */
    public function getDescendant() : Collection
    {
        return $this->descendant;
    }

    /**
     * Set parent
     *
     * @param Place $parent
     */
    public function setParent(Place $parent = null) : self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     */
    public function getParent() :? Place
    {
        return $this->parent;
    }

    /**
     * Add location
     *
     * @param Location $location
     */
    public function addLocation(Location $location) : self
    {
        $this->locations[] = $locations;

        return $this;
    }

    /**
     * Remove location
     *
     * @param Location $location
     */
    public function removeLocation(Location $location) : self
    {
        $this->locations->removeElement($location);

        return $this;
    }

    /**
     * Get locations
     */
    public function getLocations() : Collection
    {
        return $this->locations;
    }

    /**
     * Set created
     *
     * @param \DateTimeInterface $created
     */
    public function setCreated(\DateTimeInterface $created) : self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     */
    public function getCreated() :? \DateTimeInterface
    {
        return $this->created;
    }

    public function getTreeClass() : string
    {
        return Tree::class;
    }
}
