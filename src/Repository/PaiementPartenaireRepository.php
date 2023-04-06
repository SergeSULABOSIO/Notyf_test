<?php

namespace App\Repository;

use App\Entity\PaiementPartenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementPartenaire>
 *
 * @method PaiementPartenaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaiementPartenaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaiementPartenaire[]    findAll()
 * @method PaiementPartenaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementPartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementPartenaire::class);
    }

    public function save(PaiementPartenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PaiementPartenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return PaiementPartenaire[] Returns an array of PaiementPartenaire objects
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

        if ($criteres['partenaire']) {
            $query = $query
                ->andWhere('p.partenaire = :valPartenaire')
                ->setParameter('valPartenaire', $criteres['partenaire']);
        }

        $query = $query
            ->getQuery()
            ->getResult();

        //dd($query);
        //dd($criteres['police']);

        $resultPolice = [];
        if ($criteres['police']) {
            foreach ($query as $popPartenaire) {
                if ($popPartenaire->getPolice()) {
                    if ($popPartenaire->getPolice()->getId() == $criteres['police']->getId()) {
                        $resultPolice[] = $popPartenaire;
                    }
                }
            }
        } else {
            $resultPolice = $query;
        }




        //FILTRE POUR CLIENT
        $resultClient = [];
        if ($criteres['client']) {
            foreach ($resultPolice as $popPartenaire) {
                if ($popPartenaire->getPolice()) {
                    if ($popPartenaire->getPolice()->getClient()) {
                        if ($popPartenaire->getPolice()->getClient()->getId() == $criteres['client']->getId()) {
                            $resultClient[] = $popPartenaire;
                        }
                    }
                }
            }
        } else {
            $resultClient = $resultPolice;
        }

        $resultFinal = $resultClient;
        
        
        //chargement des données sur l'agregat
        if ($agregat !== null) {
            $montant = 0;
            $codeMonnaie = "";
            foreach ($resultFinal as $popPartenaire) {
                $montant += $popPartenaire->getMontant();
                if ($popPartenaire->getMonnaie()) {
                    $codeMonnaie = $popPartenaire->getMonnaie()->getCode();
                }
            }
            $agregat->setMontant($montant);
            $agregat->setCodeMonnaie($codeMonnaie);
        }

        return $resultFinal;
    }
}
