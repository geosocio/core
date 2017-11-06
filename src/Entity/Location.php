<?php

namespace App\Entity;

use App\Entity\Place\Place;
use GeoSocio\EntityUtils\ParameterBag;
use GeoSocio\EntityUtils\CreatedTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * GeoSocio\Entity\Location
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="location")
 */
class Location
{

    use CreatedTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="location_id", type="string", length=255)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var Place
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Place\Place", inversedBy="locations")
     * @ORM\JoinColumn(name="place_id", referencedColumnName="place_id")
     */
    private $place;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=8, scale=6, nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=true)
     */
    private $longitude;

    /**
     * Create new Location.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $params = new ParameterBag($data);
        $this->id = $params->getString('id');
        $this->label = $params->getString('label');
        $this->place = $params->getInstance('place', Place::class);
        $this->latitude = (float) $params->getNumber('latitude') ?: null;
        $this->longitude = (float) $params->getNumber('longitude') ?: null;
        $this->created = $params->getInstance('created', \DateTimeInterface::class);
    }

    /**
     * Get id
     *
     * @Groups({"read_anonymous"})
     */
    public function getId() :? string
    {
        return $this->id;
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
     * Get label
     *
     * @Groups({"read_anonymous"})
     */
    public function getLabel() :? string
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label) : self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the place id.
     *
     * @Groups({"read_anonymous"})
     */
    public function getPlaceId() :? int
    {
        if (!$this->place) {
            return null;
        }

        return $this->place->getId();
    }

    /**
     * Set place
     *
     * @param Place $place
     */
    public function setPlace(Place $place) : self
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return Place
     */
    public function getPlace() :? Place
    {
        return $this->place;
    }

    /**
     * Get places.
     */
    public function getPlaces() : Collection
    {
        if (!$$this->place) {
            return new ArrayCollection();
        }


        $criteria = Criteria::create()
            ->orderBy(["depth" => "DESC"]);

        return $this->place->getAncestors()->matching($criteria)->map(function ($item) {
            return $item->getAncestor();
        });
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     */
    public function setLatitude(float $latitude) : self
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @Groups({"read_anonymous"})
     */
    public function getLatitude() :? float
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     */
    public function setLongitude(float $longitude) : self
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @Groups({"read_anonymous"})
     */
    public function getLongitude() :? float
    {
        return $this->longitude;
    }
}
