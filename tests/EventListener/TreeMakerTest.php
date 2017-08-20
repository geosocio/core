<?php

namespace App\Tests\EventListener;

use App\Entity\Place\Place;
use App\Entity\Place\Tree;
use App\EventListener\TreeMaker;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;

class TreeMakerTest extends TestCase
{
    /**
     * Tests Post Persist.
     */
    public function testPostPersist()
    {
        $treeMaker = new TreeMaker();

        $parent = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();

        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getTreeClass')
            ->willReturn(Tree::class);

        $repository = $this->createMock(ObjectRepository::class);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->with(Tree::class)
            ->willReturn($repository);

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args->expects($this->once())
            ->method('getEntity')
            ->willReturn($place);
        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($em);

        $result = $treeMaker->postPersist($args);

        $this->assertNull($result);
    }

    /**
     * Test Pre Update.
     */
    public function testPreUpdate()
    {
        $treeMaker = new TreeMaker();

        $parent_id = 321;
        $parent = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $parent->method('getId')
            ->willReturn($parent_id);

        $id = 123;
        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $place->method('getId')
            ->willReturn($id);
        $place->method('getParent')
            ->willReturn($parent);
        $place->method('getTreeClass')
            ->willReturn(Tree::class);

        $original_parent_id = 456;
        $original_parent = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $original_parent->method('getId')
            ->willReturn($original_parent_id);

        $original = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();
        $original->method('getId')
            ->willReturn($id);
        $original->method('getParent')
            ->willReturn($original_parent);

        $treeRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $treeRepository->method('__call')
            ->willreturn([]);

        $placeRepository = $this->createMock(ObjectRepository::class);
        $placeRepository->method('find')
            ->with($id)
            ->willReturn($original);

        $expr = $this->getMockBuilder(Expr::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $qb->method('expr')
            ->willReturn($expr);
        $qb->method('getQuery')
            ->willReturn($query);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [
                    Tree::class,
                    $treeRepository,
                ],
                [
                    get_class($place),
                    $placeRepository,
                ],
            ]);

        $em->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args->expects($this->once())
            ->method('getEntity')
            ->willReturn($place);
        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($em);

        $result = $treeMaker->preUpdate($args);

        $this->assertNull($result);
    }

    /**
     * Test Pre Remove.
     */
    public function testPreRemove()
    {
        $treeMaker = new TreeMaker();

        $place = $this->getMockBuilder(Place::class)
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expr = $this->getMockBuilder(Expr::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $qb->method('expr')
            ->willReturn($expr);
        $qb->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);

        $args = $this->getMockBuilder(LifecycleEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $args->expects($this->once())
            ->method('getEntity')
            ->willReturn($place);
        $args->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($em);

        $result = $treeMaker->preRemove($args);

        $this->assertNull($result);
    }
}
