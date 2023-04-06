<?php

namespace App\Repository;

use App\Entity\PaiementCommission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementCommission>
 *
 * @method PaiementCommission|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaiementCommission|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaiementCommission[]    findAll()
 * @method PaiementCommission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementCommissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementCommission::class);
    }

    public function save(PaiementCommission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PaiementCommission $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return PaiementCommission[] Returns an array of PaiementCommission objects
     */
    public function findByMotCle($criteres, $agregat): array
    {
        //    return $this->createQueryBuilder('p')
        //        ->andWhere('p.exampleField = :val')
        //        ->setParameter('val', $value)
        //        ->orderBy('p.id', 'ASC')
        //        ->setMaxResults(10)
        //        ->getQuery()
        //        ->getResult()
        //    ;

        $query = $this->createQueryBuilder('p')
            ->where('p.refnotededebit like :valMotCle')
            ->orWhere('p.description like :valMotCle')
            ->setParameter('valMotCle', '%' . $criteres['motcle'] . '%')
            ->orderBy('p.id', 'DESC');

        if (($criteres['dateA'] != null) and ($criteres['dateB'] != null)) {
            $query = $query
                ->andWhere('p.date BETWEEN :valDateA AND :valDateB')
                ->setParameter('valDateA', $criteres['dateA'])
                ->setParameter('valDateB', $criteres['dateB']);
        }

        $query = $query
            ->getQuery()
            ->getResult();

        //dd($query);
        //dd($criteres['police']);


        //FILTRE POUR POLICES
        $resultPolices = [];
        if ($criteres['police']) {
            foreach ($query as $popCommission) {
                //dd($popTaxe);
                //dd($criteres['police']);
                //dd($popTaxe->getPolices());
                if ($popCommission->getPolice()) {
                    //dd($police);
                    //dd($criteres['police']->getReference());
                    if ($popCommission->getPolice()->getId() == $criteres['police']->getId()) {
                        $resultPolices[] = $popCommission;
                    }
                }
            }
        } else {
            $resultPolices = $query;
        }


        //FILTRE POUR CLIENT
        $resultClient = [];
        if ($criteres['client']) {
            foreach ($resultPolices as $popCommission) {
                if ($popCommission->getPolice()) {
                    if ($popCommission->getPolice()->getClient()) {
                        if ($popCommission->getPolice()->getClient()->getId() == $criteres['client']->getId()) {
                            $resultClient[] = $popCommission;
                        }
                    }
                }
            }
        } else {
            $resultClient = $resultPolices;
        }



        //FILTRE POUR PARTENAIRE
        $resultPartenaire = [];
        if ($criteres['partenaire']) {
            foreach ($resultClient as $popCommission) {
                if ($popCommission->getPolice()) {
                    if ($popCommission->getPolice()->getPartenaire()) {
                        if ($popCommission->getPolice()->getPartenaire()->getId() == $criteres['partenaire']->getId()) {
                            $resultPartenaire[] = $popCommission;
                        }
                    }
                }
            }
        } else {
            $resultPartenaire = $resultClient;
        }



        //FILTRE POUR PARTENAIRE
        $resultAssureur = [];
        if ($criteres['assureur']) {
            foreach ($resultPartenaire as $popCommission) {
                $police = $popCommission->getPolice();
                if ($police != null) {
                    $assureur = $police->getAssureur();
                    if ($assureur != null) {
                        if ($assureur->getId() == $criteres['assureur']->getId()) {
                            $resultAssureur[] = $popCommission;
                        }
                    }
                }
            }
        } else {
            $resultAssureur = $resultPartenaire;
        }

        $resultFinal = $resultAssureur;




        //chargement des données sur l'agregat
        if ($agregat !== null) {
            $montantRecu = 0;
            $montantNet = 0;
            $tva = 0;
            $arca = 0;
            $codeMonnaie = "";
            foreach ($resultFinal as $popCommission) {
                $net_plus_arca_temp = $popCommission->getMontant() / 1.16;
                $tva_temp = $net_plus_arca_temp * (16/100);
                $arca_temp = $net_plus_arca_temp * (2/100);
                $net_temp = $net_plus_arca_temp - $arca_temp;

                $montantRecu += $popCommission->getMontant();
                $tva += $tva_temp;
                $arca += $arca_temp;
                $montantNet += $net_temp;

                if ($popCommission->getMonnaie()) {
                    $codeMonnaie = $popCommission->getMonnaie()->getCode();
                }
            }
            //PRIMES
            $agregat->setMontantRecu($montantRecu);
            $agregat->setMontantNet($montantNet);
            $agregat->setTva($tva);
            $agregat->setArca($arca);
            $agregat->setCodeMonnaie($codeMonnaie);
        }
        return $resultFinal;
    }
}
