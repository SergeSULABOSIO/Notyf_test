<?php

namespace App\Service\RefactoringJS\Commandes\Piste;

use App\Entity\Piste;
use App\Entity\Sinistre;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ComGenerateTitreReportingCRM implements Commande
{
    private $total_piste_caff_esperes = 0;

    public function __construct(
        private $crud,
        private ServiceMonnaie $serviceMonnaie,
        private AdminUrlGenerator $adminUrlGenerator,
        private Piste $piste
    ) {
    }

    public function executer()
    {
        //dd($this->adminUrlGenerator->get("codeReporting"));
        if ($this->adminUrlGenerator->get("codeReporting") != null) {
            //SINISTRE
            if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PISTE_TOUS) {
                $this->total_piste_caff_esperes += $this->piste->getMontant();
                $com_ttc = 0;
                $prime_ttc = 0;
                // foreach ($this->piste->getCotations() as $cotation) {
                //     /** @var Cotation */
                //     $cota = $cotation;
                //     // if ($cota->getPolice() != null) {
                //     //     /** @var Police */
                //     //     $pol = $cota->getPolice();
                //     //     //On force le calcul des champs calculables
                //     //     $this->serviceCalculateur->updatePoliceCalculableFileds($pol);
                //     //     $prime_ttc += $pol->getPrimetotale();
                //     //     $com_ttc += $pol->calc_revenu_ttc;
                //     //     //dd($pol);
                //     // }
                // }

                if ($this->crud) {
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " \n
                    [
                        Revenus potentiels: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_piste_caff_esperes) . ",
                        Revenus générés: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($com_ttc) . ",
                        Primes générées: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($prime_ttc) . "
                    ]");
                }
            }
        }
    }
}
