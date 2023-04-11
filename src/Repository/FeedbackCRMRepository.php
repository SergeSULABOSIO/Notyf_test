<?php

namespace App\Repository;

use App\Entity\FeedbackCRM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedbackCRM>
 *
 * @method FeedbackCRM|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedbackCRM|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedbackCRM[]    findAll()
 * @method FeedbackCRM[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedbackCRMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedbackCRM::class);
    }

    public function save(FeedbackCRM $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FeedbackCRM $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FeedbackCRM[] Returns an array of FeedbackCRM objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FeedbackCRM
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
