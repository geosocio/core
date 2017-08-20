<?php

namespace App\Repository\Post;

use Doctrine\ORM\EntityRepository;
use App\Entity\Site;
use App\Entity\Place\Place;

class PostRepository extends EntityRepository
{
    public function findBySitePlace(Site $site = null, Place $place = null, $limit = 0, $offset = 0)
    {
        $qb = $this->createQueryBuilder('post');

        $qb->select('post, MAX(placement.created) as HIDDEN created')
            ->leftJoin('post.placements', 'placement')
            ->andWhere($qb->expr()->isNull('post.deleted'))
            ->groupBy('post.id')
            ->orderBy('created', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($place) {
            // Get all of the pace ids to query against.
            $placeIds = $place->getDescendants()->map(function ($tree) {
                return $tree->getDescendant()->getId();
            })->toArray();

            $qb->andWhere($qb->expr()->in('placement.place', $placeIds));
        }

        if ($site) {
            $qb->andWhere($qb->expr()->eq('post.site', ':siteId'))
                ->setParameter('siteId', $site->getId());
        }

        return $qb->getQuery()->getResult();
    }
}
