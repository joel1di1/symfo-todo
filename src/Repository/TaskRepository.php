<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findAllNotCompleted(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status != :status or t.status IS NULL')
            ->setParameter('status', 'complete')
            ->orderBy('t.due_date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllToday(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.due_date <= :today')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('t.due_date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllToReview(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.due_date IS NULL')
            ->andWhere('t.status <> :status')
            ->setParameter('status', 'complete')
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Task[] Returns an array of Task objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Task
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
