<?php

namespace App\Service;

use App\Entity\Police;
use App\Entity\Entreprise;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use App\Entity\PaiementTaxe;
use App\Entity\Partenaire;
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

    public function updatePoliceCalculableFileds(?Police $police)
    {
        $this->calculerRevenusHT($police);
        $this->calculerTaxes($police);
        $this->calculerRevenusTTC($police);
        $this->calculerRevenusEncaisses($police);
        $this->calculerRevenusPartageables($police);
        $this->calculerRetrocommissions($police);
        $this->calculerRevenusReserve($police);
    }

    public function calculerRevenusReserve(?Police $police){
        $police->calc_revenu_reserve = $police->calc_revenu_partageable - $police->calc_retrocom;
    }

    public function calculerRevenusPartageables(?Police $police)
    {
        $police->calc_revenu_partageable = $police->calc_revenu_ht - $police->calc_taxes_courtier;
    }

    private function calculerRevenusHT(?Police $police)
    {
        $police->calc_revenu_ht = $police->getLocalcom() + $police->getFrontingcom() + $police->getRicom();
    }

    private function calculerRevenusTTC(?Police $police)
    {
        $police->calc_revenu_ttc = $police->calc_revenu_ht + $police->calc_taxes_assureurs;
    }

    private function calculerRevenusEncaisses(?Police $police)
    {
        foreach ($this->paiements_com as $paiement_com) {
            if ($paiement_com->getPolice() == $police) {
                $police->calc_revenu_ttc_encaisse += $paiement_com->getMontant();
                $police->calc_revenu_ttc_encaisse_tab_ref_factures[] = "Réf.:" . $paiement_com->getRefnotededebit() . ", " . $paiement_com->getMonnaie()->getCode() . " " . $paiement_com->getMontant() . ", reçus de " . $paiement_com->getPolice()->getAssureur()->getNom() . " le " . $paiement_com->getDate()->format('d/m/Y') . ", enregistré par " . $paiement_com->getUtilisateur()->getNom();
            }
        }
        $police->calc_revenu_ttc_solde_restant_du = $police->calc_revenu_ttc - $police->calc_revenu_ttc_encaisse;
    }

    private function calculerRetrocommissions(?Police $police)
    {
        $retrocom_ri = 0;
        $retrocom_local = 0;
        $retrocom_fronting = 0;

        $partenaire = $police->getPartenaire();
        //dd($partenaire->getNom());
        if ($partenaire != null) {
            $part = $partenaire->getPart();

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
            $police->calc_retrocom = $retrocom_ri + $retrocom_local + $retrocom_fronting;
        }
        //dd($police->calc_retrocom . " ** " . $police->getLocalcom());

        foreach ($this->paiements_retrocom as $paiement_retrocom) {
            //dd($paiement_retrocom->getPolice());
            if ($police == $paiement_retrocom->getPolice()) {
                $police->calc_retrocom_payees += $paiement_retrocom->getMontant();
                $police->calc_retrocom_payees_tab_factures[] = "Réf.:" . $paiement_retrocom->getRefnotededebit() . ", " . $paiement_retrocom->getMonnaie()->getCode() . " " . $paiement_retrocom->getMontant() . ", reversé à " . $paiement_retrocom->getPartenaire()->getNom() . " le " . $paiement_retrocom->getDate()->format('d/m/Y') . ", enregistré par " . $paiement_retrocom->getUtilisateur()->getNom();
            }
        }
        $police->calc_retrocom_solde = $police->calc_retrocom - $police->calc_retrocom_payees;
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

    private function calculerTaxes(?Police $police)
    {
        foreach ($this->taxes as $taxe) {
            if ($taxe->isPayableparcourtier() == true) {
                //dd($taxe);
                $police->calc_taxes_courtier_tab[] = $taxe;
                $police->calc_taxes_courtier += ($police->calc_revenu_ht * $taxe->getTaux());
                foreach ($this->paiements_taxes as $paiement_taxe) {
                    if ($paiement_taxe->getTaxe() == $taxe && $paiement_taxe->getPolice() == $police) {
                        $police->calc_taxes_courtier_payees += $paiement_taxe->getMontant();
                        $police->calc_taxes_courtier_payees_tab_ref_factures[] = "Réf.:" . $paiement_taxe->getRefnotededebit() . ", " . $paiement_taxe->getMonnaie()->getCode() . " " . $paiement_taxe->getMontant() . ", reversé le " . $paiement_taxe->getDate()->format('d/m/Y') . ", enregistré par " . $paiement_taxe->getUtilisateur()->getNom();//$paiement_taxe->getRefnotededebit();
                    }
                }
                $police->calc_taxes_courtier_solde += ($police->calc_taxes_courtier - $police->calc_taxes_courtier_payees);
            } else {
                $police->calc_taxes_assureurs_tab[] = $taxe;
                $police->calc_taxes_assureurs += ($police->calc_revenu_ht * $taxe->getTaux());
                foreach ($this->paiements_taxes as $paiement_taxe) {
                    if ($paiement_taxe->getTaxe() == $taxe && $paiement_taxe->getPolice() == $police) {
                        $police->calc_taxes_assureurs_payees += $paiement_taxe->getMontant();
                        $police->calc_taxes_assureurs_payees_tab_ref_factures[] = "Réf.:" . $paiement_taxe->getRefnotededebit() . ", " . $paiement_taxe->getMonnaie()->getCode() . " " . $paiement_taxe->getMontant() . ", reversé le " . $paiement_taxe->getDate()->format('d/m/Y') . ", enregistré par " . $paiement_taxe->getUtilisateur()->getNom();//$paiement_taxe->getRefnotededebit();
                    }
                }
                $police->calc_taxes_assureurs_solde += ($police->calc_taxes_assureurs - $police->calc_taxes_assureurs_payees);
            }
        }
    }
}
