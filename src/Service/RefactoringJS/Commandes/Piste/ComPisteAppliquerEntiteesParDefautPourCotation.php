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

        //Génération des tranches par défaut
        $this->genererTranches();

        //Génération des chargements par défaut
        $this->genererChargements();

        //Génération des revenus par défaut
        $this->genererRevenus();
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
            if($typeRevenu == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_REA] || $typeRevenu == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_LOCALE]){
                $brokerage->setBase(RevenuCrudController::TAB_BASE[RevenuCrudController::BASE_PRIME_NETTE]);
            }else if($typeRevenu == RevenuCrudController::TAB_TYPE[RevenuCrudController::TYPE_COM_FRONTING]){
                $brokerage->setBase(RevenuCrudController::TAB_BASE[RevenuCrudController::BASE_FRONTING]);
            }else{
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
        //PRIME NETTE
        /** @var Chargement */
        $chargement_prime_nette = new Chargement();
        $chargement_prime_nette->setType(
            ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_PRIME_NETTE]
        );
        $chargement_prime_nette->setCreatedAt(new \DateTimeImmutable("now"));
        $chargement_prime_nette->setUpdatedAt(new \DateTimeImmutable("now"));
        $chargement_prime_nette->setUtilisateur($this->piste->getUtilisateur());
        $chargement_prime_nette->setEntreprise($this->piste->getEntreprise());
        $chargement_prime_nette->setCotation($this->cotation);
        $chargement_prime_nette->setMontant(0);
        $chargement_prime_nette->setDescription("Prime nette");
        $this->cotation->addChargement($chargement_prime_nette);


        //FRONTING
        /** @var Chargement */
        $chargement_fronting = new Chargement();
        $chargement_fronting->setType(
            ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRONTING]
        );
        $chargement_fronting->setCreatedAt(new \DateTimeImmutable("now"));
        $chargement_fronting->setUpdatedAt(new \DateTimeImmutable("now"));
        $chargement_fronting->setUtilisateur($this->piste->getUtilisateur());
        $chargement_fronting->setEntreprise($this->piste->getEntreprise());
        $chargement_fronting->setCotation($this->cotation);
        $chargement_fronting->setMontant(0);
        $chargement_fronting->setDescription("Frais fronting (frais de cession)");
        $this->cotation->addChargement($chargement_fronting);

        //ACCESSOIRES
        /** @var Chargement */
        $chargement_accessoire = new Chargement();
        $chargement_accessoire->setType(
            ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_ACCESSOIRES]
        );
        $chargement_accessoire->setCreatedAt(new \DateTimeImmutable("now"));
        $chargement_accessoire->setUpdatedAt(new \DateTimeImmutable("now"));
        $chargement_accessoire->setUtilisateur($this->piste->getUtilisateur());
        $chargement_accessoire->setEntreprise($this->piste->getEntreprise());
        $chargement_accessoire->setCotation($this->cotation);
        $chargement_accessoire->setMontant(0);
        $chargement_accessoire->setDescription("Frais administratifs");
        $this->cotation->addChargement($chargement_accessoire);

        //ARCA ou Frais de surveillance
        /** @var Chargement */
        $chargement_arca = new Chargement();
        $chargement_arca->setType(
            ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_FRAIS_DE_SURVEILLANCE_ARCA]
        );
        $chargement_arca->setCreatedAt(new \DateTimeImmutable("now"));
        $chargement_arca->setUpdatedAt(new \DateTimeImmutable("now"));
        $chargement_arca->setUtilisateur($this->piste->getUtilisateur());
        $chargement_arca->setEntreprise($this->piste->getEntreprise());
        $chargement_arca->setCotation($this->cotation);
        $chargement_arca->setMontant(0);
        $chargement_arca->setDescription("Frais de surveillance / Autorité");
        $this->cotation->addChargement($chargement_arca);

        //TVA
        /** @var Chargement */
        $chargement_tva = new Chargement();
        $chargement_tva->setType(
            ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE[ChargementCrudController::TYPE_TVA]
        );
        $chargement_tva->setCreatedAt(new \DateTimeImmutable("now"));
        $chargement_tva->setUpdatedAt(new \DateTimeImmutable("now"));
        $chargement_tva->setUtilisateur($this->piste->getUtilisateur());
        $chargement_tva->setEntreprise($this->piste->getEntreprise());
        $chargement_tva->setCotation($this->cotation);
        $chargement_tva->setMontant(0);
        $chargement_tva->setDescription("TVA / Autorité");
        $this->cotation->addChargement($chargement_tva);
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
        //On set la piste
        $this->cotation->setPiste($this->piste);
    }
}
