<?php

namespace App\Service;

use DateTime;
use DateInterval;
use App\Entity\Piste;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Cotation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Admin\PoliceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ServiceAvenant
{
    public function __construct(
        private ServiceDates $serviceDates,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
    }

    /**
     * Elle reçoit un tableau d'avenant d'une police ensuite, elle identifie l'avenant de souscription. S'il existe plusieurs avenants de souscription, seul le premier du groupe sera retourner.
     *
     * @param [Police] $policesConcernees
     * @return Police
     */
    public function getPoliceSouscription($policesConcernees): Police
    {
        foreach ($policesConcernees as $police) {
            /** @var Police */
            $pol = $police;
            if ($pol->getTypeavenant() == PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_SOUSCRIPTION]) {
                return $pol;
            }
        }
        return null;
    }


    /**
     * Cette fonction initialise une attributs d'une souscription.
     *
     * @param [type] $entite
     * @param array $avenant_data
     * @return void
     */
    public function setSouscription($entite, array $avenant_data)
    {
        $entite->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[$avenant_data['type']]);
        if ($entite instanceof Police) {
            $entite->setIdavenant(0);
            $entite->setModepaiement(0);
            $entite->setRemarques("Souscription.");
            $entite->setReassureurs("Voir le traité de réassurance en place.");
            //Champs des valeurs numériques
            $entite->setPartExceptionnellePartenaire(0);
            $entite->setCapital(1000000.00);
            $entite->setPrimenette(0);
            $entite->setFronting(0);
            $entite->setArca(0);
            $entite->setTva(0);
            $entite->setFraisadmin(0);
            $entite->setPrimetotale(0);
            $entite->setDiscount(0);
            $entite->setRicom(0);
            $entite->setFrontingcom(0);
            $entite->setLocalcom(0);
            //Mode de Partage
            $entite->setCansharericom(false);
            $entite->setCansharefrontingcom(false);
            $entite->setCansharelocalcom(false);
            //Le redevable
            $entite->setRicompayableby(0);
            $entite->setFrontingcompayableby(0);
            $entite->setLocalcompayableby(0);
            //Les Dates
            $entite->setDateoperation(new \DateTimeImmutable("now"));
            $entite->setDateemission(new \DateTimeImmutable("now"));
            $entite->setDateeffet(new \DateTimeImmutable("now"));
            $entite->setDateexpiration(new DateTimeImmutable("+365 day"));
            $entite->setUpdatedAt(new \DateTimeImmutable("now"));
            $entite->setCreatedAt(new \DateTimeImmutable("now"));
            //les associations ou clés étrangères
            $entite->setUtilisateur($this->serviceEntreprise->getUtilisateur());
            $entite->setGestionnaire($this->serviceEntreprise->getUtilisateur());
            $entite->setClient($entite->getClient());
            $entite->setProduit($entite->getProduit());
            $entite->setPartenaire(null);
            $entite->setAssureur($entite->getAssureur());
            //Les soldes calculables
            $entite->setUnpaidcommission(0);
            $entite->setUnpaidretrocommission(0);
            $entite->setUnpaidtaxeassureur(0);
            $entite->setUnpaidtaxecourtier(0);
            $entite->setUnpaidtaxe(0);
            //Autres soldes calculables
            $entite->setPaidcommission(0);
            $entite->setPaidretrocommission(0);
            $entite->setPaidtaxeassureur(0);
            $entite->setPaidtaxecourtier(0);
            $entite->setPaidtaxe(0);
        }
        return $entite;
    }

    /**
     * Cette fonction reinitialise les champs numérique de l'objet
     *
     * @param Police $police
     * @return Police
     */
    public function initialiserChampsNumerique(Police $police): Police
    {
        return $police
            ->setCapital(0)
            ->setPrimenette(0)
            ->setFronting(0)
            ->setArca(0)
            ->setTva(0)
            ->setFraisadmin(0)
            ->setPrimetotale(0)
            ->setDiscount(0)
            ->setRicom(0)
            ->setLocalcom(0)
            ->setFrontingcom(0);
    }

    /**
     * Cette fonction permet d'initialiser une Incorporation
     *
     * @param [type] $entite
     * @param array $avenant_data
     * @param AdminUrlGenerator $adminUrlGenerator
     * @return void
     */
    public function setIncorporation($entite, array $avenant_data, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[$avenant_data['type']]);
        if ($entite instanceof Cotation) {
            /** @var Piste */
            $piste = $this->entityManager->getRepository(Piste::class)->find($adminUrlGenerator->get(ServiceCrossCanal::CROSSED_ENTITY_PISTE));
            //dd($piste->getPolice());

            /** @var Cotation */
            $entite->setNom($avenant_data['type'] . " - " . Date("dmYHis") . " - " . $piste->getPolice());
            $entite->setUtilisateur($this->serviceEntreprise->getUtilisateur());
            $entite->setEntreprise($this->serviceEntreprise->getEntreprise());
            $entite->setAssureur($piste->getPolice()->getAssureur());
            $entite->setClient($piste->getPolice()->getClient());
            $entite->setProduit($piste->getPolice()->getProduit());
            $entite->setPiste($piste);
            $entite->setCreatedAt(new \DateTimeImmutable("now"));
            $entite->setUpdatedAt(new \DateTimeImmutable("now"));
        }
        if ($entite instanceof Police) {
            /** @var Police */
            $policeDeBase = $this->entityManager->getRepository(Police::class)->find($avenant_data['police']);
            $policesConcernees = $this->entityManager->getRepository(Police::class)->findBy(
                [
                    'reference' => $avenant_data['reference'],
                    'entreprise' => $this->serviceEntreprise->getEntreprise()
                ]
            );
            //On tente de récupérer l'avenant de souscription
            $policeDeBase = $this->getPoliceSouscription($policesConcernees);

            $entite->setIdavenant(count($policesConcernees));
            $entite->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[$avenant_data['type']]);
            $entite->setReference($policeDeBase->getReference());
            $entite->setDateoperation($this->serviceDates->aujourdhui());
            $entite->setDateemission($this->serviceDates->aujourdhui());
            $entite->setDateeffet($this->serviceDates->aujourdhui());
            $entite->setDateexpiration($policesConcernees[count($policesConcernees)-1]->getDateexpiration());
            $entite->setModepaiement($policeDeBase->getModepaiement());
            $entite->setRemarques("Ceci est une incorporation effectuée à la police " . $policeDeBase);
            $entite->setReassureurs($policeDeBase->getReassureurs());
            $entite->setCansharericom($policeDeBase->isCansharericom());
            $entite->setCansharefrontingcom($policeDeBase->isCansharefrontingcom());
            $entite->setCansharelocalcom($policeDeBase->isCansharelocalcom());
            $entite->setRicompayableby($policeDeBase->getRicompayableby());
            $entite->setFrontingcompayableby($policeDeBase->getFrontingcompayableby());
            $entite->setLocalcompayableby($policeDeBase->getLocalcompayableby());
            $entite->setUpdatedAt($this->serviceDates->aujourdhui());
            $entite->setCreatedAt($this->serviceDates->aujourdhui());
            $entite->setUtilisateur($this->serviceEntreprise->getUtilisateur());
            $entite->setGestionnaire($policeDeBase->getGestionnaire());
            $entite->setPartExceptionnellePartenaire($policeDeBase->getPartExceptionnellePartenaire());
            $entite->setClient($policeDeBase->getClient());
            $entite->setProduit($policeDeBase->getProduit());
            $entite->setPartenaire($policeDeBase->getPartenaire());
            $entite->setAssureur($policeDeBase->getAssureur());
            //Initialisation des variables numériques
            $entite->setCapital(0);
            $entite->setPrimenette(0);
            $entite->setFronting(0);
            $entite->setArca(0);
            $entite->setTva(0);
            $entite->setFraisadmin(0);
            $entite->setPrimetotale(0);
            $entite->setDiscount(0);
            $entite->setRicom(0);
            $entite->setLocalcom(0);
            $entite->setFrontingcom(0);
        }
        return $entite;
    }




    /**
     * Cette fonction permet d'initialiser un Renouvellement
     *
     * @param [type] $entite
     * @param array $avenant_data
     * @param AdminUrlGenerator $adminUrlGenerator
     * @return void
     */
    public function setRenouvellement($entite, array $avenant_data, AdminUrlGenerator $adminUrlGenerator)
    {
        $entite->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[$avenant_data['type']]);
        if ($entite instanceof Cotation) {
            /** @var Piste */
            $piste = $this->entityManager->getRepository(Piste::class)->find($adminUrlGenerator->get(ServiceCrossCanal::CROSSED_ENTITY_PISTE));

            /** @var Cotation */
            $entite
                ->setNom($avenant_data['type'] . " - " . Date("dmYHis") . " - " . $piste->getPolice())
                ->setUtilisateur($this->serviceEntreprise->getUtilisateur())
                ->setEntreprise($this->serviceEntreprise->getEntreprise())
                ->setAssureur($piste->getPolice()->getAssureur())
                ->setClient($piste->getPolice()->getClient())
                ->setProduit($piste->getPolice()->getProduit())
                ->setPiste($piste)
                ->setCreatedAt($this->serviceDates->aujourdhui())
                ->setUpdatedAt($this->serviceDates->aujourdhui());
        }
        if ($entite instanceof Police) {
            /** @var Police */
            $policeDeBase = $this->entityManager->getRepository(Police::class)->find($avenant_data['police']);
            $policesConcernees = $this->entityManager->getRepository(Police::class)->findBy(
                [
                    'reference' => $avenant_data['reference'],
                    'entreprise' => $this->serviceEntreprise->getEntreprise()
                ]
            );
            //On tente de récupérer l'avenant de souscription
            $policeDeBase = $this->getPoliceSouscription($policesConcernees);

            $entite
                ->setIdavenant(count($policesConcernees))
                ->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[$avenant_data['type']])
                ->setReference($policeDeBase->getReference())
                ->setDateoperation($this->serviceDates->aujourdhui())
                ->setDateemission($this->serviceDates->aujourdhui())
                ->setDateeffet($this->serviceDates->ajouterJours($policeDeBase->getDateexpiration(), 1))
                ->setDateexpiration($this->serviceDates->ajouterJours($policeDeBase->getDateexpiration(), 365))
                ->setModepaiement($policeDeBase->getModepaiement())
                ->setRemarques("Renouvellement de la police " . $policeDeBase)
                ->setReassureurs($policeDeBase->getReassureurs())
                ->setCansharericom($policeDeBase->isCansharericom())
                ->setCansharefrontingcom($policeDeBase->isCansharefrontingcom())
                ->setCansharelocalcom($policeDeBase->isCansharelocalcom())
                ->setRicompayableby($policeDeBase->getRicompayableby())
                ->setFrontingcompayableby($policeDeBase->getFrontingcompayableby())
                ->setLocalcompayableby($policeDeBase->getLocalcompayableby())
                ->setUpdatedAt($this->serviceDates->aujourdhui())
                ->setCreatedAt($this->serviceDates->aujourdhui())
                ->setUtilisateur($this->serviceEntreprise->getUtilisateur())
                ->setGestionnaire($policeDeBase->getGestionnaire())
                ->setPartExceptionnellePartenaire($policeDeBase->getPartExceptionnellePartenaire())
                ->setClient($policeDeBase->getClient())
                ->setProduit($policeDeBase->getProduit())
                ->setPartenaire($policeDeBase->getPartenaire())
                ->setAssureur($policeDeBase->getAssureur())
                //Initialisation des variables numériques
                ->setCapital(0)
                ->setPrimenette(0)
                ->setFronting(0)
                ->setArca(0)
                ->setTva(0)
                ->setFraisadmin(0)
                ->setPrimetotale(0)
                ->setDiscount(0)
                ->setRicom(0)
                ->setLocalcom(0)
                ->setFrontingcom(0);
        }
        return $entite;
    }

    /**
     * Cette fonction initialise l'objet l'avenant d'annulation.
     *
     * @param Police $entite
     * @param array $avenant_data
     * @return Police
     */
    public function setAnnulation(Police $entite, array $avenant_data): Police
    {
        if ($avenant_data['police']) {
            /** @var Police */
            $policeDeBase = $this->entityManager->getRepository(Police::class)->find($avenant_data['police']);
            $policesConcernees = $this->entityManager->getRepository(Police::class)->findBy(
                [
                    'reference' => $avenant_data['reference'],
                    'entreprise' => $this->serviceEntreprise->getEntreprise()
                ]
            );
            if ($entite instanceof Police) {
                $entite->setIdavenant(count($policesConcernees));
                $entite->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[$avenant_data['type']]);
                $entite->setReference($policeDeBase->getReference());
                $entite->setDateoperation($this->serviceDates->aujourdhui());
                $entite->setDateemission($this->serviceDates->aujourdhui());
                $entite->setDateeffet($policeDeBase->getDateeffet());
                $entite->setDateexpiration($policeDeBase->getDateeffet());
                $entite->setModepaiement($policeDeBase->getModepaiement());
                $entite->setRemarques("Cette police est annulée.");
                $entite->setReassureurs($policeDeBase->getReassureurs());
                $entite->setCansharericom($policeDeBase->isCansharericom());
                $entite->setCansharefrontingcom($policeDeBase->isCansharefrontingcom());
                $entite->setCansharelocalcom($policeDeBase->isCansharelocalcom());
                $entite->setRicompayableby($policeDeBase->getRicompayableby());
                $entite->setFrontingcompayableby($policeDeBase->getFrontingcompayableby());
                $entite->setLocalcompayableby($policeDeBase->getLocalcompayableby());
                $entite->setUpdatedAt($this->serviceDates->aujourdhui());
                $entite->setCreatedAt($this->serviceDates->aujourdhui());
                $entite->setUtilisateur($this->serviceEntreprise->getUtilisateur());
                $entite->setGestionnaire($policeDeBase->getGestionnaire());
                $entite->setPartExceptionnellePartenaire($policeDeBase->getPartExceptionnellePartenaire());
                $entite->setClient($policeDeBase->getClient());
                $entite->setProduit($policeDeBase->getProduit());
                $entite->setPartenaire($policeDeBase->getPartenaire());
                $entite->setAssureur($policeDeBase->getAssureur());
                //Initialisation des variables à cumuler
                $tot_capital = 0;
                $tot_prime_nette = 0;
                $tot_fronting = 0;
                $tot_arca = 0;
                $tot_tva = 0;
                $tot_frais_admin = 0;
                $tot_prime_totale = 0;
                $tot_discount = 0;
                $tot_ricom = 0;
                $tot_localcom = 0;
                $tot_frontingcom = 0;
                foreach ($policesConcernees as $p) {
                    /** @var Police  */
                    $polco = $p;
                    //On cumule les valeurs numériques ensuite on les mutiliplie par -1 pour les annuler en un coup;
                    $tot_capital += $polco->getCapital();
                    $tot_prime_nette += $polco->getPrimenette();
                    $tot_fronting += $polco->getFronting();
                    $tot_arca += $polco->getArca();
                    $tot_tva += $polco->getTva();
                    $tot_frais_admin += $polco->getFraisadmin();
                    $tot_prime_totale += $polco->getPrimetotale();
                    $tot_discount += $polco->getDiscount();
                    $tot_ricom += $polco->getRicom();
                    $tot_localcom += $polco->getLocalcom();
                    $tot_frontingcom += $polco->getFrontingcom();
                }
                $entite->setCapital($tot_capital * -1);
                $entite->setPrimenette($tot_prime_nette * -1);
                $entite->setFronting($tot_fronting * -1);
                $entite->setArca($tot_arca * -1);
                $entite->setTva($tot_tva * -1);
                $entite->setFraisadmin($tot_frais_admin * -1);
                $entite->setPrimetotale($tot_prime_totale * -1);
                $entite->setDiscount($tot_discount * -1);
                $entite->setRicom($tot_ricom * -1);
                $entite->setLocalcom($tot_localcom * -1);
                $entite->setFrontingcom($tot_frontingcom * -1);
            }
        }
        return $entite;
    }

    /**
     * Cette fonction permet de définir le type d'avenant à un objet. Elle permet de définnir les attributs par défaut de l'objet initialisé en fonction du type d'avénant choisi.
     *
     * @param [type] $entite
     * @param AdminUrlGenerator $adminUrlGenerator
     * @return void
     */
    public function setAvenant($entite, AdminUrlGenerator $adminUrlGenerator)
    {
        if ($adminUrlGenerator) {
            if ($adminUrlGenerator->get("avenant")) {
                $avenant_data = $adminUrlGenerator->get("avenant");
                //On effectue les traitements selon le type d'avenant
                switch ($avenant_data['type']) {
                    case PoliceCrudController::AVENANT_TYPE_ANNULATION:
                        $entite = $this->setAnnulation($entite, $avenant_data);
                        break;
                    case PoliceCrudController::AVENANT_TYPE_SOUSCRIPTION:
                        $entite = $this->setSouscription($entite, $avenant_data);
                        break;
                    case PoliceCrudController::AVENANT_TYPE_INCORPORATION:
                        $entite = $this->setIncorporation($entite, $avenant_data, $adminUrlGenerator);
                        break;
                    case PoliceCrudController::AVENANT_TYPE_RENOUVELLEMENT:
                        $entite = $this->setRenouvellement($entite, $avenant_data, $adminUrlGenerator);
                        break;

                    default:
                        dd("Avenant non supporté!!!! (" . $avenant_data['type'] . ")");
                        break;
                }
                //dd($police);

            }
        }
        return $entite;
    }
}
