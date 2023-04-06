<?php

namespace App\Repository;

use App\Entity\Partenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partenaire>
 *
 * @method Partenaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Partenaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Partenaire[]    findAll()
 * @method Partenaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partenaire::class);
    }

    public function save(Partenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Partenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Partenaire[] Returns an array of Partenaire objects
     */
    public function findByMotCle($criteres): array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.nom like :valMotCle')
            ->orWhere('p.adresse like :valMotCle')
            ->orWhere('p.email like :valMotCle')
            ->orWhere('p.rccm like :valMotCle')
            ->orWhere('p.idnat like :valMotCle')
            ->orWhere('p.numimpot like :valMotCle')
            ->setParameter('valMotCle', '%' . $criteres['motcle'] . '%')
            ->orderBy('p.id', 'DESC');

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

    //    public function findOneBySomeField($value): ?Partenaire
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
