<?php

namespace App\Repository;

use App\Entity\Assureur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assureur>
 *
 * @method Assureur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Assureur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Assureur[]    findAll()
 * @method Assureur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AssureurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assureur::class);
    }

    public function save(Assureur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Assureur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Assureur[] Returns an array of Assureur objects
     */
    public function findByMotCle($criteres): array
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.nom like :valMotCle')
            ->orWhere('a.adresse like :valMotCle')
            ->orWhere('a.telephone like :valMotCle')
            ->orWhere('a.email like :valMotCle')
            ->orWhere('a.rccm like :valMotCle')
            ->orWhere('a.idnat like :valMotCle')
            ->orWhere('a.numimpot like :valMotCle')
            ->setParameter('valMotCle', '%' . $criteres['motcle'] . '%')
            ->orderBy('a.id', 'DESC');

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

    

    //    public function findOneBySomeField($value): ?Assureur
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
