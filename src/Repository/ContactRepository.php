<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 *
 * @method Contact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contact[]    findAll()
 * @method Contact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function save(Contact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Contact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Contact[] Returns an array of Contact objects
     */
    public function findByMotCle($criteres): array
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.nom like :valMotCle')
            ->orWhere('c.poste like :valMotCle')
            ->orWhere('c.telephone like :valMotCle')
            ->orWhere('c.email like :valMotCle')
            ->setParameter('valMotCle', '%' . $criteres['motcle'] . '%')
            ->orderBy('c.id', 'DESC');

        if ($criteres['client']) {
            $query = $query
                ->andWhere('c.client = :valClient')
                ->setParameter('valClient', $criteres['client']);
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

    //    public function findOneBySomeField($value): ?Contact
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
