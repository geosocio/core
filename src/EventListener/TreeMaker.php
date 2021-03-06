<?php

namespace App\EventListener;

use App\Entity\TreeAwareInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Creates the Tree.
 */
class TreeMaker
{

    /**
     * Post Persist hook.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof TreeAwareInterface) {
            return;
        }

        $class = $entity->getTreeClass();

        $em = $args->getEntityManager();
        $repository = $em->getRepository($class);

        $tree = new $class([
            'ancestor' => $entity,
            'descendant' => $entity,
            'depth' => 0,
        ]);

        $em->persist($tree);

        if ($parent = $entity->getParent()) {
            // Get the tree of the entity's parent.
            $parent_tree = $repository->findByDescendant($parent);

            foreach ($parent_tree as $tree) {
                $new_tree = clone $tree;
                $new_tree->setDescendant($entity);
                $new_tree->setDepth($tree->getDepth() + 1);
                $em->persist($new_tree);
            }
        }

        $em->flush();
    }

    /**
     * Pre Update
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();

        if (!$entity instanceof TreeAwareInterface) {
            return;
        }

        $class = $entity->getTreeClass();

        $em = $args->getEntityManager();
        $repository = $em->getRepository(get_class($entity));

        $original = $repository->find($entity->getId());

        $original_parent = $original->getParent();
        $new_parent = $entity->getParent();

        $original_parent_id = $original_parent->getId();
        $new_parent_id = $new_parent->getId();

        // Make sure there was a change in Parents before proceeding.
        if ($original_parent_id === $new_parent_id) {
            return;
        }

        $repository = $em->getRepository($class);

        // First Get the comment's tree below itself.
        $descendants = $repository->findByAncestor($entity->getID());

        $descendant_ids = array();
        foreach ($descendants as $descendant) {
            $descendant_ids[] = $descendant->getId();
        }

        // Then delete the comment tree above the current comment.
        $qb = $em->createQueryBuilder();
        $qb->delete($class, 't');
        $qb->where($qb->expr()->in('t.descendant', $descendant_ids));
        $qb->where($qb->expr()->notIn('t.ancestor', $descendant_ids));
        $q = $qb->getQuery();
        $q->execute();

        // Finally, copy the tree from the new parent.
        $parent_tree = $repository->findByDescendant($new_parent);

        foreach ($parent_tree as $tree) {
            foreach ($descendants as $descendant) {
                $new_tree = clone $tree;
                $new_tree->setDescendant($descendant);
                $new_tree->setDepth($tree->getDepth() + $descendant->getDepth() + 1);
                $em->persist($new_tree);
            }
        }

        $em->flush();
    }

    /**
     * Pre Remove.
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof TreeAwareInterface) {
            return;
        }

        $class = $entity->getTreeClass();

        $em = $args->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->delete($class, 't');
        $qb->where($qb->expr()->orX(
            $qb->expr()->eq('t.ancestor', $entity->getId()),
            $qb->expr()->eq('t.descendant', $entity->getId())
        ));
        $q = $qb->getQuery();
        $q->execute();
    }
}
