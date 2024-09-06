<?php

namespace App\Service\RefactoringJS\Commandes;

use App\Entity\Sinistre;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class ComGenerateTitreReportingSinistre implements Commande
{
    //SINISTRE
    public $total_sinistre_cout = 0;
    public $total_sinistre_indemnisation = 0;
    public $total_piste_caff_esperes = 0;

    public function __construct(
        private $crud,
        private ServiceMonnaie $serviceMonnaie,
        private AdminUrlGenerator $adminUrlGenerator,
        private Sinistre $sinistre
    ) {
    }

    public function executer()
    {
        //dd($this->adminUrlGenerator->get("codeReporting"));
        if ($this->adminUrlGenerator->get("codeReporting") != null) {
            //SINISTRE
            if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_SINISTRE_TOUS) {
                $this->total_sinistre_cout += $this->sinistre->getCout();
                $this->total_sinistre_indemnisation += $this->sinistre->getMontantPaye();

                if ($this->crud) {
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " \n
                    [
                        Dégâts estimés: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_sinistre_cout) . ", 
                        Compensation versée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_sinistre_indemnisation) . "
                    ]");
                }
            }
        }
    }
}
