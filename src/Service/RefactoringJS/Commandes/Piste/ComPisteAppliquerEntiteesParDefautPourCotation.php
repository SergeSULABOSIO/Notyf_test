<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Entity\Tranche;
use App\Entity\Cotation;
use App\Entity\Chargement;
use App\Service\RefactoringJS\Commandes\Commande;
use App\Controller\Admin\ChargementCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Entity\Revenu;
use App\Service\RefactoringJS\Evenements\Observateur;

class ComPisteAppliquerEntiteesParDefautPourCotation implements Commande
{
    public function __construct(private ?Piste $piste, private ?Cotation $cotation)
    {
    }

    public function executer()
    {
        //Génération autres attributs par défaut
        $this->genererAutresAttibuts();

        //Si et seulement si la cotation n'a âs de tranches
        if (count($this->cotation->getTranches()) == 0) {
            //Génération des tranches par défaut
            $this->genererTranches();
        }

        //Si et seulement si la cotation n'a pas de chargements
        if (count($this->cotation->getChargements()) == 0) {
            //Génération des chargements par défaut
            $this->genererChargements();
        }

        //Si et seulement si la cotation n'a pas de revenu
        if (count($this->cotation->getRevenus()) == 0) {
            //Génération des revenus par défaut
            $this->genererRevenus();
        }
    }

    private function genererRevenus()
    {
        //COMMISSION DE REASSURANCE
        foreach (RevenuCrudController::TAB_TYPE as $typeRevenu) {
            // dd("RAS", $typeRevenu);
            /** @var Revenu */
            $brokerage = new Revenu();
            $brokerage->setType($typeRevenu);
            $brokerage->setPartageable(true);
            $brokerage->setTaxable(true);
            $brokerage->setIsparttranche(true);
            $brokerage->setIspartclient(false);
            if ($typeRevenu == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_REA] || $typeRevenu == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_LOCALE]) {
                $brokerage->setBase(RevenuCrudController::TAB_BASE[RevenuCrudController::BASE_PRIME_NETTE]);
            } else if ($typeRevenu == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_FRONTING]) {
                $brokerage->setBase(RevenuCrudController::TAB_BASE[RevenuCrudController::BASE_FRONTING]);
            } else {
                $brokerage->setBase(RevenuCrudController::TAB_BASE[RevenuCrudController::BASE_MONTANT_FIXE]);
            }
            $brokerage->setTaux(0);
            $brokerage->setMontantFlat(0);
            $brokerage->setCreatedAt(new \DateTimeImmutable("now"));
            $brokerage->setUpdatedAt(new \DateTimeImmutable("now"));
            $brokerage->setUtilisateur($this->piste->getUtilisateur());
            $brokerage->setEntreprise($this->piste->getEntreprise());
            $brokerage->setCotation($this->cotation);
            $this->cotation->addRevenu($brokerage);
        }
    }

    private function genererChargements()
    {
        //On set les chargements par defaut.
        foreach (ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE as $typeChargement) {
            /** @var Chargement */
            $chargement = new Chargement();
            $chargement->setType($typeChargement);
            $chargement->setCreatedAt(new \DateTimeImmutable("now"));
            $chargement->setUpdatedAt(new \DateTimeImmutable("now"));
            $chargement->setUtilisateur($this->piste->getUtilisateur());
            $chargement->setEntreprise($this->piste->getEntreprise());
            $chargement->setCotation($this->cotation);
            $chargement->setMontant(0);
            if ($typeChargement == ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE]) {
                $chargement->setDescription("Prime nette");
            } else if ($typeChargement == ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING]) {
                $chargement->setDescription("Frais fronting (frais de cession)");
            } else if ($typeChargement == ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRAIS_DE_SURVEILLANCE_ARCA]) {
                $chargement->setDescription("Frais de surveillance / Autorité");
            } else if ($typeChargement == ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_TVA]) {
                $chargement->setDescription("TVA / Autorité");
            } else if ($typeChargement == ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_ACCESSOIRES]) {
                $chargement->setDescription("Frais administratifs");
            } else {
                $chargement->setDescription("Autre chargement");
            }
            $this->cotation->addChargement($chargement);
        }
    }

    private function genererTranches()
    {
        //On set les tranches par défaut
        /** @var Tranche */
        $tranche = new Tranche();
        $tranche->setNom("Tranche n°01");
        $tranche->setTaux(1);
        $tranche->setUtilisateur($this->piste->getUtilisateur());
        $tranche->setEntreprise($this->piste->getEntreprise());
        $tranche->setDateEffet(new \DateTimeImmutable("now"));
        $tranche->setDateExpiration(new \DateTimeImmutable("+364 days"));
        $tranche->setCreatedAt(new \DateTimeImmutable("now"));
        $tranche->setUpdatedAt(new \DateTimeImmutable("now"));
        $tranche->setDuree(($this->cotation->getDureeCouverture()));
        $tranche->setCotation($this->cotation);
        $this->cotation->addTranch($tranche);
    }

    private function genererAutresAttibuts()
    {
        //On set le partenaire
        if ($this->piste->getPartenaire() != null) {
            $this->cotation->setPartenaire($this->piste->getPartenaire());
            $this->cotation->setTauxretrocompartenaire($this->piste->getPartenaire()->getPart());
        }
        //On set la validité
        if ($this->cotation->isValidated() == null) {
            $this->cotation->setValidated(false);
        }
        //On set l'entreprise
        if ($this->piste->getEntreprise() != null) {
            $this->cotation->setEntreprise($this->piste->getEntreprise());
        }
        //On set le client
        if ($this->piste->getClient() != null) {
            $this->cotation->setClient($this->piste->getClient());
        }
        //On set le produit
        if ($this->piste->getProduit() != null) {
            $this->cotation->setProduit($this->piste->getProduit());
        }
        //On set la police
        if ($this->piste->getPolice() != null) {
            $this->cotation->setPolice($this->piste->getPolice());
        }
        //On set aussi le gestionnaire de compte
        if ($this->piste->getGestionnaire() != null) {
            $this->cotation->setGestionnaire($this->piste->getGestionnaire());
        }
        //On set la piste
        $this->cotation->setPiste($this->piste);
    }
}
