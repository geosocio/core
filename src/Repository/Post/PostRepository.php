<?php

namespace GeoSocio\Core\Repository\Post;

use Doctrine\ORM\EntityRepository;
use GeoSocio\Core\Entity\Place\Place;
use GeoSocio\Core\Entity\Post\Post;

class PostRepository extends EntityRepository
{
    public function findByPlace(Place $place)
    {
        return $this->createQueryBuilder('post')
            ->select('post, MAX(placement.created) as HIDDEN created')
            ->leftJoin('post.placements', 'placement')
            ->where('placement.place = :place_id')
            ->setParameter('place_id', $place->getId())
            ->andWhere('post.deleted IS NULL')
            ->groupBy('post.id')
            ->orderBy('created', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
