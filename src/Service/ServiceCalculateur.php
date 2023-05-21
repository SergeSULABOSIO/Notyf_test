<?php

namespace App\Service;

use App\Entity\Police;
use App\Entity\Entreprise;
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
    public function __construct(private EntityManagerInterface $entityManager, private ServiceEntreprise $serviceEntreprise) {
        
    }

    public function updatePoliceCalculableFileds(?Police $police){
        $this->calculerRevenus($police);
        $this->calculerRetrocommissions($police);
        $this->calculerTaxes($police);
    }

    private function calculerRevenus(?Police $police)
    {
        //SECTION - REVENU
        $police->calc_revenu_ht = 1+1;
        $police->calc_revenu_ttc = 0;
        $police->calc_revenu_ttc_encaisse = 0;
        $police->calc_revenu_ttc_encaisse_tab_ref_factures = [];
        $police->calc_revenu_ttc_encaisse_tab_dates = [];
        $police->calc_revenu_ttc_solde_restant_du = 0;
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
                    
                }

                $police->calc_taxes_courtier_payees = 0;
                $police->calc_taxes_courtier_payees_tab_ref_factures = [];
                $police->calc_taxes_courtier_payees_tab_dates = [];


                $police->calc_taxes_courtier_solde = ($police->calc_taxes_courtier - $police->calc_taxes_courtier_payees);
            }
        }

        

        

        $police->calc_taxes_assureurs = 0;
        $police->calc_taxes_assureurs_payees = 0;
        $police->calc_taxes_assureurs_payees_tab_ref_factures = [];
        $police->calc_taxes_assureurs_payees_tab_dates = [];
        $police->calc_taxes_assureurs_solde = 0;
    }

}
