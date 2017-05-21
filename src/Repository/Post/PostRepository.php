<?php

namespace GeoSocio\Core\Repository\Post;

use Doctrine\ORM\EntityRepository;
use GeoSocio\Core\Entity\Place\Place;

class PostRepository extends EntityRepository
{
    public function findByPlace(Place $place)
    {
        $qb = $this->createQueryBuilder('post');

        // Get all of the pace ids to query against.
        $placeIds = $place->getDescendants()->map(function ($tree) {
            return $tree->getDescendant()->getId();
        })->toArray();

        // Does not perform permission checks.
        return $qb->select('post, MAX(placement.created) as HIDDEN created')
            ->leftJoin('post.placements', 'placement')
            ->where($qb->expr()->in('placement.place', $placeIds))
            ->andWhere($qb->expr()->isNull('post.deleted'))
            ->groupBy('post.id')
            ->orderBy('created', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
