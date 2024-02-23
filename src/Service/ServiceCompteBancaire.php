<?php

namespace App\Service;

use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Entity\CompteBancaire;
use App\Entity\Entreprise;
use App\Entity\Facture;
use App\Entity\Monnaie;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use NumberFormatter;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceCompteBancaire
{
    private ?Utilisateur $utilisateur = null;
    private ?Entreprise $entreprise = null;
    private $comptesBancaires = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
        //Chargement de l'utilisateur et de l'entreprise
        $this->utilisateur = $this->serviceEntreprise->getUtilisateur();
        $this->entreprise = $this->serviceEntreprise->getEntreprise();

        //Chargement des monnaies
        $this->chargerComptes();
    }

    private function chargerComptes()
    {
        $this->comptesBancaires = $this->entityManager->getRepository(CompteBancaire::class)->findBy(
            ['entreprise' => $this->entreprise]
        );
    }

    public function getComptesBancaireByMonnaie($codeMonnaie)
    {
        $tab = [];
        foreach ($this->comptesBancaires as $compte) {
            /** @var CompteBancaire */
            $cb = $compte;
            if ($codeMonnaie == "") {
                $tab[] = $cb;
            } else if ($cb->getCodeMonnaie() == $codeMonnaie) {
                $tab[] = $cb;
            }
        }
        return $tab;
    }

    public function getTypeFacture(int $typeFacture){
        foreach (FactureCrudController::TAB_TYPE_FACTURE as $key => $value) {
            if($typeFacture === $value){
                return $key;
            }
        }
        return null;
    }

    public function setComptes(?Facture $facture, $codeMonnaie)
    {
        $add = false;
        if($facture->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_CLIENT]){
            $add = true;
        }else if($facture->getDestination() == FactureCrudController::TAB_DESTINATION[FactureCrudController::DESTINATION_ASSUREUR]){
            $add = true;
        }
        if($add == true){
            foreach ($this->getComptesBancaireByMonnaie($codeMonnaie) as $cb) {
                $facture->addCompteBancaire($cb);
            }
        }
    }
}
