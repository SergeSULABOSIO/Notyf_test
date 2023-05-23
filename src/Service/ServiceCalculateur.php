<?php

namespace App\Service;

use App\Entity\Police;
use App\Entity\Entreprise;
use App\Entity\PaiementCommission;
use App\Entity\PaiementTaxe;
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
    public function __construct(private EntityManagerInterface $entityManager, private ServiceEntreprise $serviceEntreprise)
    {
    }

    public function updatePoliceCalculableFileds(?Police $police)
    {
        $this->calculerRevenusHT($police);
        $this->calculerTaxes($police);
        $this->calculerRevenusTTC($police);
        $this->calculerRevenusEncaisses($police);
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
        $paiements_com = $this->entityManager->getRepository(PaiementCommission::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        foreach ($paiements_com as $paiement_com) {
            $police->calc_revenu_ttc_encaisse += $paiement_com->getMontant();
            $police->calc_revenu_ttc_encaisse_tab_ref_factures[] = $paiement_com->getRefnotededebit();
            $police->calc_revenu_ttc_encaisse_tab_dates[] = $paiement_com->getDate()->format('d/m/Y à H:m:s');
        }
        $police->calc_revenu_ttc_solde_restant_du = $police->calc_revenu_ttc - $police->calc_revenu_ttc_encaisse;
    }

    private function calculerRetrocommissions(?Police $police)
    {
        //SECTION - PARTENAIRES
        $police->calc_retrocom = 0;
        $police->calc_retrocom_payees = 0;
        $police->calc_retrocom_payees_tab_factures = [];
        $police->calc_retrocom_payees_tab_dates = [];
        $police->calc_retrocom_solde = 0;
    }

    private function calculerTaxes(?Police $police)
    {
        //SECTION - TAXES
        $taxes = $this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        $paiements_taxes = $this->entityManager->getRepository(PaiementTaxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );

        foreach ($taxes as $taxe) {
            if ($taxe->isPayableparcourtier() == true) {
                //dd($taxe);
                $police->calc_taxes_courtier += ($police->calc_revenu_ht * $taxe->getTaux());
                foreach ($paiements_taxes as $paiement_taxe) {
                    if ($paiement_taxe->getTaxe() == $taxe) {
                        $police->calc_taxes_courtier_payees += $paiement_taxe->getMontant();
                        $police->calc_taxes_courtier_payees_tab_ref_factures[] = $paiement_taxe->getRefnotededebit();
                        $police->calc_taxes_courtier_payees_tab_dates[] = $paiement_taxe->getDate()->format('d/m/Y à H:m:s');
                    }
                }
                $police->calc_taxes_courtier_solde += ($police->calc_taxes_courtier - $police->calc_taxes_courtier_payees);
            } else {
                $police->calc_taxes_assureurs += ($police->calc_revenu_ht * $taxe->getTaux());
                foreach ($paiements_taxes as $paiement_taxe) {
                    if ($paiement_taxe->getTaxe() == $taxe) {
                        $police->calc_taxes_assureurs_payees += $paiement_taxe->getMontant();
                        $police->calc_taxes_assureurs_payees_tab_ref_factures[] = $paiement_taxe->getRefnotededebit();
                        $police->calc_taxes_assureurs_payees_tab_dates[] = $paiement_taxe->getDate()->format('d/m/Y à H:m:s');
                    }
                }
                $police->calc_taxes_assureurs_solde += ($police->calc_taxes_assureurs - $police->calc_taxes_assureurs_payees);
            }
        }
    }
}
