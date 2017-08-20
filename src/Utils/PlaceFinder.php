<?php

namespace App\Utils;

use App\Client\Mapzen\SearchInterface;
use App\Client\Mapzen\WhosOnFirstInterface;
use App\Entity\Location;
use App\Entity\Place\Place;
use GeoSocio\Slugger\SluggerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use GuzzleHttp\Exception\ClientException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Place Finder.
 */
class PlaceFinder implements PlaceFinderInterface
{

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @var WhosOnFirstInterface
     */
    protected $whosonfirst;

    /**
     * @var SluggerInterface
     */
    protected $slugger;

    /**
     * Creates a Place Finder.
     *
     * @param RegistryInterface $doctrine
     * @param SearchInterface $search
     * @param WhosOnFirstInterface $whosonfirst
     */
    public function __construct(
        RegistryInterface $doctrine,
        SearchInterface $search,
        WhosOnFirstInterface $whosonfirst,
        SluggerInterface $slugger
    ) {

        $this->doctrine = $doctrine;
        $this->search = $search;
        $this->whosonfirst = $whosonfirst;
        $this->slugger = $slugger;
    }

    /**
     * {@inheritdoc}
     */
    public function find(Location $input) : Location
    {
        $em = $this->doctrine->getEntityManager();
        $repository = $em->getRepository(Location::class);

        // Get all of the details from Mapzen.
        $input = $this->search->get($input->getId())->wait();

        if (!$input->getPlace()) {
            throw new \Exception('Place is missing from input');
        }

        // Check to see if a location already exists.
        $location = $repository->find($input->getId());
        if (!$location) {
            $location = new Location([
                'id' => $input->getId(),
                'latitude' => $input->getLatitude(),
                'longitude' => $input->getLongitude(),
            ]);
            $em->persist($location);
            $em->flush();
        }

        // Loop through the ancestors to find a place since it's possible for a
        // place to not exist.
        $place = null;
        try {
            $place = $this->getPlace($input->getPlace());
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() !== 404) {
                throw $e;
            }
            $ancestor = $input->getPlace()->getAncestors()->first();
            while ($ancestor) {
                try {
                    $place = $this->getPlace($ancestor->getAncestor());
                    break;
                } catch (ClientException $e) {
                    if ($e->getResponse()->getStatusCode() !== 404) {
                        throw $e;
                    }
                    $ancestor = $input->getPlace()->getAncestors()->next();
                    continue;
                }
            }
        }

        if (!$place) {
            throw new \Exception("No Place Found.");
        }

        $location->setPlace($place);
        $em->flush();

        return $location;
    }

    /**
     * Gets a Place
     *
     * @param Place $input
     */
    protected function getPlace(Place $input) : Place
    {
        $em = $this->doctrine->getEntityManager();
        $repository = $em->getRepository(Place::class);
        $input = $this->whosonfirst->get($input->getId())->wait();

        $place = $repository->find($input->getId());

        $parent = null;
        if ($input->getParent()) {
            $parent = $this->getPlace($input->getParent());
        }

        if ($parent && $place) {
            $place->setParent($parent);
            $em->flush();
        }

        if (!$place) {
            $place = new Place([
                'id' => $input->getId(),
                'slug' => $this->slugger->slug($input->getName()),
                'parent' => $parent,
                'name' => $input->getName(),
            ]);

            foreach ($this->getSlugs($place) as $slug) {
                try {
                    $place->setSlug($slug);
                    $em->persist($place);
                    $em->flush();
                    break;
                } catch (UniqueConstraintViolationException $e) {
                    $em = $this->doctrine->resetManager();
                    if ($parent = $place->getParent()) {
                        $parent = $em->merge($parent);
                        $place->setParent($parent);
                    }
                    // Try again.
                }
            }

            if (!$repository->find($place->getId())) {
                throw new \Exception("Place was not created");
            }
        }

        return $place;
    }

    /**
     * Get slug appends.
     *
     * @param Place $place
     * @param string $previous
     * @param array $slugs
     */
    protected function getSlugs(Place $place, string $previous = '', array $slugs = []) : array
    {
        if (!$previous) {
            $previous = $place->getSlug();
            $slugs[] = $previous;
        }

        if ($parent = $place->getParent()) {
            $slug = $previous . '-' . $parent->getSlug();
            $slugs[] = $slug;
            $slugs = $this->getSlugs($parent, $slug, $slugs);
        }

        return $slugs;
    }
}
