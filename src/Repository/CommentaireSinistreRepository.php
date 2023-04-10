<?php

namespace App\Repository;

use App\Entity\CommentaireSinistre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommentaireSinistre>
 *
 * @method CommentaireSinistre|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommentaireSinistre|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommentaireSinistre[]    findAll()
 * @method CommentaireSinistre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaireSinistreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommentaireSinistre::class);
    }

    public function save(CommentaireSinistre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CommentaireSinistre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CommentaireSinistre[] Returns an array of CommentaireSinistre objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CommentaireSinistre
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
