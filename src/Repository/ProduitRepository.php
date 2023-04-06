<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function save(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Produit[] Returns an array of Produit objects
     */
    public function findByMotCle($criteres): array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.nom like :valMotCle')
            ->orWhere('p.description like :valMotCle')
            ->setParameter('valMotCle', '%' . $criteres['motcle'] . '%')
            ->orderBy('p.id', 'DESC');

        if ($criteres['categorie'] != -1) {
            $query = $query
                ->andWhere('p.categorie = :valCategorie')
                ->setParameter('valCategorie', $criteres['categorie']);
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
            ->getSingleScalarResult();
    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
