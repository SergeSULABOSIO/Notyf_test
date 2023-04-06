<?php

namespace App\Repository;

use App\Entity\Automobile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Automobile>
 *
 * @method Automobile|null find($id, $lockMode = null, $lockVersion = null)
 * @method Automobile|null findOneBy(array $criteria, array $orderBy = null)
 * @method Automobile[]    findAll()
 * @method Automobile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutomobileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Automobile::class);
    }

    public function save(Automobile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Automobile $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Automobile[] Returns an array of Automobile objects
     */
    public function findByMotCle($criteres): array
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.model like :valMotCle')
            ->orWhere('a.marque like :valMotCle')
            ->orWhere('a.plaque like :valMotCle')
            ->orWhere('a.chassis like :valMotCle')
            ->setParameter('valMotCle', '%' . $criteres['motcle'] . '%')
            ->orderBy('a.id', 'DESC');

        if ($criteres['nature'] != -1) {
            $query = $query
                ->andWhere('a.nature = :valNature')
                ->setParameter('valNature', $criteres['nature']);
        }

        if ($criteres['utilite'] != -1) {
            $query = $query
                ->andWhere('a.utilite = :valUtilite')
                ->setParameter('valUtilite', $criteres['utilite']);
        }

        $query = $query
            ->getQuery()
            ->getResult();

        return $query;
    }

    public function stat_get_nombres_enregistrements()
       {
           return $this->createQueryBuilder('a')
               ->select('count(a.id) as nombre')
            //    ->select('a.exampleField = :val')
            //    ->setParameter('val', $value)
               ->getQuery()
               ->getSingleScalarResult()
           ;
       }

    //    public function findOneBySomeField($value): ?Automobile
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
