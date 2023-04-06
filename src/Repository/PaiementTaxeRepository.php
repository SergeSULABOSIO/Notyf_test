<?php

namespace App\Repository;

use App\Entity\PaiementTaxe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\Array_;

/**
 * @extends ServiceEntityRepository<PaiementTaxe>
 *
 * @method PaiementTaxe|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaiementTaxe|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaiementTaxe[]    findAll()
 * @method PaiementTaxe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementTaxeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementTaxe::class);
    }

    public function save(PaiementTaxe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PaiementTaxe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return PaiementTaxe[] Returns an array of PaiementTaxe objects
     */
    public function findByMotCle($criteres, $agregat): array
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.refnotededebit like :valMotCle')
            ->setParameter('valMotCle', '%' . $criteres['motcle'] . '%')
            ->orderBy('p.id', 'DESC');

        if (($criteres['dateA'] != null) and ($criteres['dateB'] != null)) {
            $query = $query
                ->andWhere('p.date BETWEEN :valDateA AND :valDateB')
                ->setParameter('valDateA', $criteres['dateA'])
                ->setParameter('valDateB', $criteres['dateB']);
        }

        if ($criteres['taxe']) {
            $query = $query
                ->andWhere('p.taxe = :valTaxe')
                ->setParameter('valTaxe', $criteres['taxe']);
        }

        $query = $query
            ->getQuery()
            ->getResult();

        //dd($query);
        //dd($criteres['police']);

        $resultFinal = [];
        if ($criteres['police']) {
            foreach ($query as $popTaxe) {
                if ($popTaxe->getPolice()) {
                    if ($popTaxe->getPolice()->getId() == $criteres['police']->getId()) {
                        $resultFinal[] = $popTaxe;
                    }
                }
            }
        } else {
            $resultFinal = $query;
        }

        
        
        //chargement des données sur l'agregat
        if ($agregat !== null) {
            $montant = 0;
            $codeMonnaie = "";
            foreach ($resultFinal as $popTaxe) {
                $montant += $popTaxe->getMontant();
                if ($popTaxe->getMonnaie()) {
                    $codeMonnaie = $popTaxe->getMonnaie()->getCode();
                }
            }
            $agregat->setMontant($montant);
            $agregat->setCodeMonnaie($codeMonnaie);
        }
        return $resultFinal;
    }

    //    public function findOneBySomeField($value): ?PaiementTaxe
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
