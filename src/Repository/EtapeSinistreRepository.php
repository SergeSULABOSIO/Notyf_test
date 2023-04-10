<?php

namespace App\Repository;

use App\Entity\EtapeSinistre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EtapeSinistre>
 *
 * @method EtapeSinistre|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtapeSinistre|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtapeSinistre[]    findAll()
 * @method EtapeSinistre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtapeSinistreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtapeSinistre::class);
    }

    public function save(EtapeSinistre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EtapeSinistre $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return EtapeSinistre[] Returns an array of EtapeSinistre objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EtapeSinistre
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
