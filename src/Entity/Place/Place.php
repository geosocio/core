<?php

namespace GeoSocio\Core\Entity\Place;

use Doctrine\Common\Collections\Criteria;
use GeoSocio\Core\Entity\Location;
use GeoSocio\Core\Entity\Entity;
use GeoSocio\Core\Entity\Post\Post;
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
     */
    private $ancestors;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Tree", mappedBy="ancestor")
     */
    private $descendants;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="GeoSocio\Core\Entity\Location", mappedBy="place",  cascade={"all"})
     */
    private $locations;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="GeoSocio\Core\Entity\Post\Post", mappedBy="place")
     */
    private $posts;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="GeoSocio\Core\Entity\Post\Placement", mappedBy="place")
     */
    private $placements;

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

        $posts = $data['posts'] ?? null;
        $this->posts = $this->getMultiple($posts, Post::class);

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
        $this->ancestors[] = $ancestor;

        return $this;
    }

    /**
     * Remove ancestor
     *
     * @param Tree $ancestor
     */
    public function removeAncestor(Tree $ancestor) : self
    {
        $this->ancestors->removeElement($ancestor);

        return $this;
    }

    /**
     * Get ancestor
     */
    public function getAncestors() : Collection
    {
        return $this->ancestors;
    }

    /**
     * Add descendant
     *
     * @param Tree $descendant
     */
    public function addDescendant(Tree $descendant) : self
    {
        $this->descendants[] = $descendant;

        return $this;
    }

    /**
     * Remove descendant
     *
     * @param Tree $descendant
     */
    public function removeDescendant(Tree $descendant) : self
    {
        $this->descendants->removeElement($descendant);
    }

    /**
     * Get descendant
     */
    public function getDescendants() : Collection
    {
        return $this->descendants;
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
     * Get parents.
     */
    public function getParents() : Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->neq("ancestor", $this))
            ->orderBy(["depth" => "DESC"]);

        return $this->ancestors->matching($criteria)->map(function ($item) {
            return $item->getAncestor();
        });
    }

    /**
     * Get parent ids.
     *
     * @Groups({"anonymous_read"})
     */
    public function getParentIds() : Collection
    {
        return $this->getParents()->map(function ($place) {
            return $place->getId();
        });
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

    /**
     * Test if place is equal.
     */
    public function isEqualTo(Place $place) : bool
    {

        if (!$this->id || !$place->getId()) {
            return false;
        }

        return true;
    }
}
