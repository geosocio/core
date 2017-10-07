<?php

namespace App\Entity\Place;

use GeoSocio\EntityUtils\CreatedTrait;
use GeoSocio\EntityUtils\ParameterBag;
use Doctrine\Common\Collections\Criteria;
use App\Entity\Location;
use App\Entity\Post\Post;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\TreeAwareInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * App\Entity\Place
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="place")
 * @ORM\Entity()
 */
class Place implements TreeAwareInterface
{

    use CreatedTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="place_id", type="integer")
     * @ORM\Id
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
     * @ORM\OneToMany(targetEntity="App\Entity\Location", mappedBy="place",  cascade={"all"})
     */
    private $locations;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Post\Post", mappedBy="place")
     */
    private $posts;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Post\Placement", mappedBy="place")
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
        $params = new ParameterBag($data);
        $this->id = $params->getInt('id');
        $this->name = $params->getString('name');
        $this->slug = $params->getString('slug');
        $this->parent = $params->getInstance('parent', Place::class);
        $this->ancestor = $params->getInstance('ancestor', Place::class);
        $this->descendant = $params->getInstance('descendant', Tree::class);
        $this->locations = $params->getCollection('locations', Location::class, new ArrayCollection());
        $this->posts = $params->getCollection('posts', Post::class, new ArrayCollection());
        $this->created = $params->getInstance('created', \DateTimeInterface::class);
    }

    /**
     * Get id
     *
     * @Groups({"read_anonymous"})
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
     *
     * @Groups({"read_anonymous"})
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
     * @param Place|null $parent
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
     * @Groups({"read_anonymous"})
     */
    public function getParentId() :? int
    {
        if ($this->parent) {
            return $this->parent->getId();
        }

        return null;
    }


    public function getTreeClass() : string
    {
        return Tree::class;
    }

    /**
     * Test if place is equal.
     *
     * @param Place $place
     */
    public function isEqualTo(Place $place) : bool
    {

        if (!$this->id || !$place->getId()) {
            return false;
        }

        return true;
    }
}
