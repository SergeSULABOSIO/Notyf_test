<?php

namespace App\Service;

use App\Entity\Assureur;
use App\Entity\CalculableEntity;
use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Entreprise;
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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceCalculateur
{
    private $paiements_com = null;
    private $taxes = null;
    private $paiements_taxes = null;
    private $paiements_retrocom = null;
    private $polices = null;

    public const RUBRIQUE_POLICE = 0;
    public const RUBRIQUE_PARTENAIRE = 1;
    public const RUBRIQUE_PRODUIT = 2;
    public const RUBRIQUE_CLIENT = 3;
    public const RUBRIQUE_ASSUREUR = 4;
    public const RUBRIQUE_PISTE = 5;
    public const RUBRIQUE_TAXE = 6;
    public const RUBRIQUE_SINISTRE = 7;


    public function __construct(private EntityManagerInterface $entityManager, private ServiceEntreprise $serviceEntreprise)
    {
        $this->paiements_com = $this->entityManager->getRepository(PaiementCommission::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        $this->taxes = $this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        $this->paiements_taxes = $this->entityManager->getRepository(PaiementTaxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        $this->paiements_retrocom = $this->entityManager->getRepository(PaiementPartenaire::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
    }

    public function calculate($container, $rubrique)
    {
        switch ($rubrique) {
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

            case self::RUBRIQUE_CLIENT:
                $entityManager = $container->get('doctrine')->getManagerForClass(Client::class);
                $liste = $entityManager->getRepository(Client::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updateClientCalculableFileds($pol);
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

            case self::RUBRIQUE_PISTE:
                $entityManager = $container->get('doctrine')->getManagerForClass(Piste::class);
                $liste = $entityManager->getRepository(Piste::class)->findBy(
                    ['entreprise' => $this->serviceEntreprise->getEntreprise()]
                );
                foreach ($liste as $pol) {
                    $this->updatePisteCalculableFileds($pol);
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

            default:
                # code...
                break;
        }
    }

    public function updatePoliceCalculableFileds(?CalculableEntity $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'id' => $obj->getId()
            ]
        );
        $this->calculer($obj);
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
                'piste' => $obj
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

    public function updateSinistreCalculableFileds(?CalculableEntity $obj)
    {
        $this->calculerPolices(
            [
                'entreprise' => $this->serviceEntreprise->getEntreprise(),
                'id' => $obj->getPolice(),
            ]
        );
        $this->calculer($obj);
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
    }


    private function calculerPolices($criteres)
    {
        $this->polices = $this->entityManager->getRepository(Police::class)->findBy($criteres);
    }

    public function calculerRevenusReserve(?CalculableEntity $obj)
    {
        $obj->calc_revenu_reserve = $obj->calc_revenu_partageable - $obj->calc_retrocom;
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
            $obj->calc_polices_tab[] = $police;
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
            foreach ($this->paiements_com as $paiement_com) {
                if ($paiement_com->getPolice() == $police) {
                    $obj->calc_revenu_ttc_encaisse += $paiement_com->getMontant();
                    $obj->calc_revenu_ttc_encaisse_tab_ref_factures[] = "Réf.:" . $paiement_com->getRefnotededebit() . ", " . $paiement_com->getMontant() . ", reçus de " . $paiement_com->getPolice()->getAssureur()->getNom() . " le " . $paiement_com->getDate()->format('d/m/Y') . ", enregistré par " . $paiement_com->getUtilisateur()->getNom();
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
                    $retrocom_ri = ($this->removeBrokerTaxe($police->getRicom()) * $part) / 100;
                }
                if ($police->isCansharelocalcom() == true) {
                    //dd($this->removeBrokerTaxe($police->getRicom()) . " -- " . $police->getRicom());
                    $retrocom_local = ($this->removeBrokerTaxe($police->getLocalcom()) * $part) / 100;
                }
                if ($police->isCansharefrontingcom() == true) {
                    $retrocom_fronting = ($this->removeBrokerTaxe($police->getFrontingcom()) * $part) / 100;
                }
                $obj->calc_retrocom = $retrocom_ri + $retrocom_local + $retrocom_fronting;
            }
            //dd($police->calc_retrocom . " ** " . $police->getLocalcom());

            foreach ($this->paiements_retrocom as $paiement_retrocom) {
                //dd($paiement_retrocom->getPolice());
                if ($police == $paiement_retrocom->getPolice()) {
                    $obj->calc_retrocom_payees += $paiement_retrocom->getMontant();
                    $obj->calc_retrocom_payees_tab_factures[] = "Réf.:" . $paiement_retrocom->getRefnotededebit() . ", " . $paiement_retrocom->getMontant() . ", reversé à " . $paiement_retrocom->getPartenaire()->getNom() . " le " . $paiement_retrocom->getDate()->format('d/m/Y') . ", enregistré par " . $paiement_retrocom->getUtilisateur()->getNom();
                }
            }
            $obj->calc_retrocom_solde = $obj->calc_retrocom - $obj->calc_retrocom_payees;
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
        foreach ($this->paiements_taxes as $paiement_taxe) {
            if ($paiement_taxe->getTaxe() == $taxe && $paiement_taxe->getPolice() == $police) {
                $calculableEntity->calc_taxes_courtier_payees += $paiement_taxe->getMontant();
                $calculableEntity->calc_taxes_courtier_payees_tab_ref_factures[] = "Réf.:" . $paiement_taxe->getRefnotededebit() . ", " . $paiement_taxe->getMontant() . ", le " . $paiement_taxe->getDate()->format('d/m/Y') . ", par " . $paiement_taxe->getUtilisateur()->getNom(); //$paiement_taxe->getRefnotededebit();
            }
        }
        $calculableEntity->calc_taxes_courtier_solde += ($calculableEntity->calc_taxes_courtier - $calculableEntity->calc_taxes_courtier_payees);
    }

    public function updateDataTaxeAssureur(?CalculableEntity $calculableEntity, ?Taxe $taxe, ?Police $police)
    {
        $calculableEntity->calc_taxes_assureurs_tab[] = $taxe;
        $calculableEntity->calc_taxes_assureurs += ($calculableEntity->calc_revenu_ht * $taxe->getTaux());
        foreach ($this->paiements_taxes as $paiement_taxe) {
            if ($paiement_taxe->getTaxe() == $taxe && $paiement_taxe->getPolice() == $police) {
                $calculableEntity->calc_taxes_assureurs_payees += $paiement_taxe->getMontant();
                $calculableEntity->calc_taxes_assureurs_payees_tab_ref_factures[] = "Réf.:" . $paiement_taxe->getRefnotededebit() . ", " . $paiement_taxe->getMontant() . ", le " . $paiement_taxe->getDate()->format('d/m/Y') . ", par " . $paiement_taxe->getUtilisateur()->getNom(); //$paiement_taxe->getRefnotededebit();
            }
        }
        $calculableEntity->calc_taxes_assureurs_solde += ($calculableEntity->calc_taxes_assureurs - $calculableEntity->calc_taxes_assureurs_payees);
    }
}
