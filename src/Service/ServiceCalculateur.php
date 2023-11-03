<?php

namespace App\Service;

use App\Controller\Admin\FactureCrudController;
use App\Entity\Assureur;
use App\Entity\CalculableEntity;
use App\Entity\Client;
use App\Entity\ElementFacture;
use App\Entity\Police;
use App\Entity\Entreprise;
use App\Entity\Facture;
use App\Entity\Paiement;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use App\Entity\PaiementTaxe;
use App\Entity\Partenaire;
use App\Entity\Piste;
use App\Entity\Produit;
use App\Entity\Sinistre;
use App\Entity\Taxe;
use App\Entity\Utilisateur;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceEntreprise;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceCalculateur
{
    private $taxes = null;
    private $paiements = null;
    private $polices = null;
    private $sinistres = null;
    private $elementFactures = null;

    public const RUBRIQUE_POLICE = 0;
    public const RUBRIQUE_PARTENAIRE = 1;
    public const RUBRIQUE_PRODUIT = 2;
    public const RUBRIQUE_CLIENT = 3;
    public const RUBRIQUE_ASSUREUR = 4;
    public const RUBRIQUE_PISTE = 5;
    public const RUBRIQUE_TAXE = 6;
    public const RUBRIQUE_SINISTRE = 7;
    public const RUBRIQUE_FACTURE = 8;
    public const RUBRIQUE_ELEMENT_FACTURE = 9;


    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServiceMonnaie $serviceMonnaie
    ) {
        $this->paiements = $this->entityManager->getRepository(Paiement::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        $this->taxes = $this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
    }

    public function calculate($container, $rubrique)
    {
        switch ($rubrique) {
            case self::RUBRIQUE_CLIENT:
                $entityManager = $container->get('doctrine')->getManagerForClass(Client::class);
                $listeClients = $entityManager->getRepository(Client::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($listeClients as $client) {
                    $this->updateClientCalculableFileds($client);
                }
                break;

            case self::RUBRIQUE_POLICE:
                $entityManager = $container->get('doctrine')->getManagerForClass(Police::class);
                $liste = $entityManager->getRepository(Police::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updatePoliceCalculableFileds($pol);
                }
                break;

            case self::RUBRIQUE_PARTENAIRE:
                $entityManager = $container->get('doctrine')->getManagerForClass(Partenaire::class);
                $liste = $entityManager->getRepository(Partenaire::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updatePartenaireCalculableFileds($pol);
                }
                break;

            case self::RUBRIQUE_PRODUIT:
                $entityManager = $container->get('doctrine')->getManagerForClass(Produit::class);
                $liste = $entityManager->getRepository(Produit::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updateProduitCalculableFileds($pol);
                }
                break;

            case self::RUBRIQUE_ASSUREUR:
                $entityManager = $container->get('doctrine')->getManagerForClass(Assureur::class);
                $liste = $entityManager->getRepository(Assureur::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updateAssureurCalculableFileds($pol);
                }
                break;

            case self::RUBRIQUE_TAXE:
                $entityManager = $container->get('doctrine')->getManagerForClass(Taxe::class);
                $liste = $entityManager->getRepository(Taxe::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updateTaxeCalculableFileds($pol);
                }
                break;

            case self::RUBRIQUE_SINISTRE:
                $entityManager = $container->get('doctrine')->getManagerForClass(Sinistre::class);
                $liste = $entityManager->getRepository(Sinistre::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updateSinistreCalculableFileds($pol);
                }
                break;

            case self::RUBRIQUE_FACTURE:
                $entityManager = $container->get('doctrine')->getManagerForClass(Facture::class);
                $liste = $entityManager->getRepository(Facture::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );

                foreach ($liste as $fact) {
                    $this->updateFactureCalculableFileds($fact);
                }
                break;

            default:
                # code...
                break;
        }
    }

    public function updatePoliceCalculableFileds(?Police $police)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'id' => $police->getId()
            ]
        );
        $this->calculer($police);
        //On met à jour le status des outstanding
        //$police->setIsCommissionUnpaid(($police->calc_revenu_ttc_solde_restant_du == 0) ? false : true);
        $police->setUnpaidcommission($police->calc_revenu_ttc_solde_restant_du);
        $police->setUnpaidretrocommission($police->calc_retrocom_solde);
        $police->setUnpaidtaxecourtier($police->calc_taxes_courtier_solde);
        $police->setUnpaidtaxeassureur($police->calc_taxes_assureurs_solde);
        $police->setUnpaidtaxe($police->calc_taxes_assureurs_solde + $police->calc_taxes_courtier_solde);

        $police->setPaidcommission($police->calc_revenu_ttc_encaisse);
        $police->setPaidretrocommission($police->calc_retrocom_payees);
        $police->setPaidtaxecourtier($police->calc_taxes_courtier_payees);
        $police->setPaidtaxeassureur($police->calc_taxes_assureurs_payees);
        $police->setPaidtaxe($police->calc_taxes_assureurs_payees + $police->calc_taxes_courtier_payees);

        //dd($police->isIsCommissionUnpaid());
        $this->entityManager->persist($police);
        $this->entityManager->flush();
    }

    public function updatePartenaireCalculableFileds(?CalculableEntity $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'partenaire' => $obj
            ]
        );
        $this->calculer($obj);
    }

    public function updateProduitCalculableFileds(?CalculableEntity $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'produit' => $obj
            ]
        );
        $this->calculer($obj);
    }

    public function updateClientCalculableFileds(?CalculableEntity $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'client' => $obj
            ]
        );
        $this->calculer($obj);
    }

    public function updateAssureurCalculableFileds(?CalculableEntity $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'assureur' => $obj
            ]
        );
        $this->calculer($obj);
    }

    public function updatePisteCalculableFileds(?CalculableEntity $obj)
    {

        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                //'piste' => $obj
            ]
        );
        $this->calculer($obj);
    }

    public function updateTaxeCalculableFileds(?CalculableEntity $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise()
            ]
        );
        $this->calculer($obj);
    }

    public function updateSinistreCalculableFileds(?Sinistre $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'id' => $obj->getPolice(),
            ]
        );
        $this->calculer($obj);
    }

    public function updateFactureCalculableFileds(?Facture $facture)
    {
        $this->calculerElementFactures(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'facture' => $facture,
            ]
        );
        $this->chargerPaiements(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'facture' => $facture,
            ]
        );
        $this->calculerFactureMontantDu($facture);
        $this->calculerFactureMontantPaye($facture);
        $facture->setTotalSolde($facture->getTotalDu() - $facture->getTotalRecu());
        if ($facture->getTotalSolde() == 0) {
            $facture->setStatus(FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_SOLDEE]);
        } else if ($facture->getTotalSolde() > 0 && $facture->getTotalDu() > $facture->getTotalSolde()) {
            $facture->setStatus(FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_ENCOURS]);
        } else {
            $facture->setStatus(FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE]);
        }
        $this->entityManager->persist($facture);
        $this->entityManager->flush();
    }

    private function calculer(?CalculableEntity $obj)
    {
        $this->calculerPrimes($obj);
        $this->calculerRevenuHT($obj);
        $this->calculerTaxes($obj);
        $this->calculerRevenusTTC($obj);
        $this->calculerRevenusEncaisses($obj);
        $this->calculerRevenusPartageables($obj);
        $this->calculerRetrocommissions($obj);
        $this->calculerRevenusReserve($obj);
        $this->calculerSinistre($obj);
    }

    private function calculerFactureMontantDu(?Facture $facture)
    {
        $totDu = 0;
        foreach ($this->elementFactures as $elementFacture) {
            /** @var ElementFacture */
            $ef = $elementFacture;
            $totDu += $ef->getMontant();
        }
        $facture->setTotalDu($totDu);
    }

    private function calculerFactureMontantPaye(?Facture $facture)
    {
        $totPaid = 0;
        foreach ($this->paiements as $paiement) {
            /** @var Paiement */
            $pyt = $paiement;
            $totPaid += $pyt->getMontant();
        }
        $facture->setTotalRecu($totPaid);
    }


    private function calculerPolices($criteres)
    {

        $this->polices = $this->entityManager->getRepository(Police::class)->findBy($criteres);
        //  dd("SERGE");
    }

    private function calculerElementFactures($criteres)
    {

        $this->elementFactures = $this->entityManager->getRepository(ElementFacture::class)->findBy($criteres);
        //  dd("SERGE");
    }

    private function chargerPaiements($criteres)
    {

        $this->paiements = $this->entityManager->getRepository(Paiement::class)->findBy($criteres);
        //  dd("SERGE");
    }

    public function calculerRevenusReserve(?CalculableEntity $obj)
    {
        $obj->calc_revenu_reserve = $obj->calc_revenu_partageable - $obj->calc_retrocom;
    }

    public function calculerSinistre(?CalculableEntity $obj)
    {
        //dd($this->polices);
        foreach ($this->polices as $police) {
            /** @var Police */
            $pol = $police;
            if (count($pol->getSinistres()) != 0) {
                foreach ($pol->getSinistres() as $sinistre) {
                    /** @var Sinistre */
                    $sin = $sinistre;
                    $obj->calc_sinistre_dommage_total += $sin->getCout();
                    $obj->calc_sinistre_indemnisation_total += $sin->getMontantPaye();
                }
            }
        }
        //dd("Prime totale: " . $obj->calc_polices_primes_totale);
        if ($obj->calc_polices_primes_totale != 0) {
            $obj->calc_sinistre_indice_SP = round($obj->calc_sinistre_indemnisation_total / $obj->calc_polices_primes_totale, 3);
        }
        //dd("Sinistre payé: " . $obj->calc_sinistre_indice_SP*100 . "%");
    }

    public function calculerRevenusPartageables(?CalculableEntity $obj)
    {
        $obj->calc_revenu_partageable = $obj->calc_revenu_ht - $obj->calc_taxes_courtier;
    }

    private function calculerRevenuHT(?CalculableEntity $obj)
    {
        foreach ($this->polices as $police) {
            $obj->calc_revenu_ht += $police->getLocalcom() + $police->getFrontingcom() + $police->getRicom();
        }
    }

    private function calculerPrimes(?CalculableEntity $obj)
    {
        foreach ($this->polices as $police) {
            //Meta - police
            $obj->calc_polices_accessoire += $police->getFraisAdmin();
            $obj->calc_polices_tva += $police->getTva();
            $obj->calc_polices_primes_nette += $police->getPrimenette();
            $obj->calc_polices_primes_totale += $police->getPrimetotale();
            $obj->calc_polices_fronting += $police->getFronting();
        }
    }

    private function calculerRevenusTTC(?CalculableEntity $obj)
    {
        $obj->calc_revenu_ttc = $obj->calc_revenu_ht + $obj->calc_taxes_assureurs;
    }

    private function calculerRevenusEncaisses(?CalculableEntity $obj)
    {
        foreach ($this->polices as $police) {
            /** @var Paiement */
            foreach ($this->paiements as $paiement) {
                if ($paiement->getFacture()) {
                    foreach ($paiement->getFacture()->getElementFactures() as $elementFacture) {
                        if ($elementFacture->getPolice() === $police) {

                            $totDue = $paiement->getFacture()->getTotalDu() / 100;
                            $totPaid = $paiement->getMontant() / 100;
                            $proportionPaid = ($totPaid / $totDue);

                            switch ($paiement->getFacture()->getType()) {
                                case FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_COMMISSIONS]:
                                    $obj->calc_revenu_ttc_encaisse = $obj->calc_revenu_ttc_encaisse + $elementFacture->getMontant();
                                    dd("J'ai trouvé le paiement de commission de courtage. Montant = " . ($obj->calc_revenu_ttc_encaisse/100) * $proportionPaid . " (dû: " . $totDue . " vs payé:" . $totPaid . ")");
                                    
                                    break;
                                case FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION]:
                                    dd("J'ai trouvé le paiement de frais de gestion. Taux de paiement = " . $proportionPaid . " (dû: " . $totDue . " vs payé:" . $totPaid . ")");
                                    break;
                                case FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA]:
                                    dd("J'ai trouvé le paiement de frais Arca. Taux de paiement = " . $proportionPaid . " (dû: " . $totDue . " vs payé:" . $totPaid . ")");
                                    break;
                                case FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA]:
                                    dd("J'ai trouvé le paiement de la Tva. Taux de paiement = " . $proportionPaid . " (dû: " . $totDue . " vs payé:" . $totPaid . ")");
                                    break;
                                case FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS]:
                                    dd("J'ai trouvé le paiement de frais de gestion. Taux de paiement = " + $proportionPaid . " (dû: " . $totDue . " vs payé:" . $totPaid . ")");
                                    break;

                                default:
                                    # code...
                                    break;
                            }
                            //$obj->calc_revenu_ttc_encaisse += $proportionPaid * $obj->calc_revenu_ttc_solde_restant_du;
                        }
                    }
                }
            }
            $obj->calc_revenu_ttc_solde_restant_du = $obj->calc_revenu_ttc - $obj->calc_revenu_ttc_encaisse;
        }
    }

    private function calculerRetrocommissions(?CalculableEntity $obj)
    {
        foreach ($this->polices as $police) {
            $retrocom_ri = 0;
            $retrocom_local = 0;
            $retrocom_fronting = 0;

            $partenaire = $police->getPartenaire();
            //dd($partenaire->getNom());
            if ($partenaire != null) {
                if ($police->getPartExceptionnellePartenaire() != 0) {
                    $part = $police->getPartExceptionnellePartenaire() * 100;
                } else {
                    $part = $partenaire->getPart();
                }

                if ($police->isCansharericom() == true) {
                    $retrocom_ri = ($this->removeBrokerTaxe($police->getRicom()) * $part);
                }
                if ($police->isCansharelocalcom() == true) {
                    //dd($this->removeBrokerTaxe($police->getRicom()) . " -- " . $police->getRicom());
                    $retrocom_local = ($this->removeBrokerTaxe($police->getLocalcom()) * $part);
                }
                if ($police->isCansharefrontingcom() == true) {
                    $retrocom_fronting = ($this->removeBrokerTaxe($police->getFrontingcom()) * $part);
                }
                $obj->calc_retrocom += $retrocom_ri + $retrocom_local + $retrocom_fronting;
            }
            //dd($obj->calc_retrocom);

            /** @var Paiement */
            foreach ($this->paiements as $paiement) {
                if ($paiement->getFacture()) {
                    foreach ($paiement->getFacture()->getElementFactures() as $elementFacture) {
                        if ($police === $elementFacture->getPolice()) {
                            $totDue = $paiement->getFacture()->getTotalDu() / 100;
                            $totPaid = $paiement->getMontant() / 100;
                            $proportionPaid = ($totPaid / $totDue);
                            $obj->calc_retrocom_payees += $proportionPaid * $obj->calc_retrocom_solde;
                        }
                    }
                }
            }
            $obj->calc_retrocom_solde = $obj->calc_retrocom - $obj->calc_retrocom_payees;
            //dd($obj->calc_retrocom_solde);
        }
    }

    private function removeBrokerTaxe($netCommission)
    {
        foreach ($this->taxes as $taxe) {
            if ($taxe->isPayableparcourtier() == true) {
                //dd($taxe->getTaux());
                $netCommission = $netCommission - ($netCommission * $taxe->getTaux());
            }
        }

        return $netCommission;
    }

    private function calculerTaxes(?CalculableEntity $calculableEntity)
    {
        //dd($calculableEntity);
        foreach ($this->polices as $police) {
            foreach ($this->taxes as $taxe) {
                if ($calculableEntity instanceof Taxe) {
                    if ($taxe->getId() == $calculableEntity->getId()) {
                        if ($taxe->isPayableparcourtier() == true) {
                            //dd($taxe);
                            $this->updateDataTaxeCourtier($calculableEntity, $taxe, $police);
                        } else {
                            $this->updateDataTaxeAssureur($calculableEntity, $taxe, $police);
                        }
                    }
                } else {
                    if ($taxe->isPayableparcourtier() == true) {
                        //dd($taxe);
                        $this->updateDataTaxeCourtier($calculableEntity, $taxe, $police);
                    } else {
                        $this->updateDataTaxeAssureur($calculableEntity, $taxe, $police);
                    }
                }
            }
        }
    }

    public function updateDataTaxeCourtier(?CalculableEntity $calculableEntity, ?Taxe $taxe, ?Police $police)
    {
        $calculableEntity->calc_taxes_courtier_tab[] = $taxe;
        $calculableEntity->calc_taxes_courtier += ($calculableEntity->calc_revenu_ht * $taxe->getTaux());
        /** @var Paiement */
        foreach ($this->paiements as $paiement) {
            if ($paiement->getFacture()) {
                foreach ($paiement->getFacture()->getElementFactures() as $elementFacture) {
                    if ($elementFacture->getPolice() === $police) {
                        $totDue = $paiement->getFacture()->getTotalDu() / 100;
                        $totPaid = $paiement->getMontant() / 100;
                        $proportionPaid = ($totPaid / $totDue);
                        $calculableEntity->calc_taxes_courtier_payees += $proportionPaid * $calculableEntity->calc_taxes_courtier_solde;
                    }
                }
            }
        }
        $calculableEntity->calc_taxes_courtier_solde += ($calculableEntity->calc_taxes_courtier - $calculableEntity->calc_taxes_courtier_payees);
    }

    public function updateDataTaxeAssureur(?CalculableEntity $calculableEntity, ?Taxe $taxe, ?Police $police)
    {
        $calculableEntity->calc_taxes_assureurs_tab[] = $taxe;
        $calculableEntity->calc_taxes_assureurs += ($calculableEntity->calc_revenu_ht * $taxe->getTaux());
        /** @var Paiement */
        foreach ($this->paiements as $paiement) {
            if ($paiement->getFacture()) {
                foreach ($paiement->getFacture()->getElementFactures() as $elementFacture) {
                    if ($elementFacture->getPolice() === $police) {
                        $totDue = $paiement->getFacture()->getTotalDu() / 100;
                        $totPaid = $paiement->getMontant() / 100;
                        $proportionPaid = ($totPaid / $totDue);
                        $calculableEntity->calc_taxes_assureurs_payees += $proportionPaid * $calculableEntity->calc_taxes_assureurs_solde;
                    }
                }
            }
        }
        $calculableEntity->calc_taxes_assureurs_solde += ($calculableEntity->calc_taxes_assureurs - $calculableEntity->calc_taxes_assureurs_payees);
    }
}
