<?php

namespace App\Service\RefactoringJS\JSUIComponents\Monnaie;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\MonnaieCrudController;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;

class MonnaieFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Code
        $this->addChamp(
            (new JSChamp())
            ->createChoix("code", "Code")
            ->setColumns(6)
            ->setChoices(MonnaieCrudController::TAB_MONNAIES)
            ->getChamp()
        );
        
        //Fonction
        $this->addChamp(
            (new JSChamp())
            ->createChoix("fonction", "Fonction Système")
            ->setColumns(2)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_FONCTIONS)
            ->getChamp()
        );
        
        //Taux en USD
        $this->addChamp(
            (new JSChamp())
            ->createArgent("tauxusd", "Taux (en USD)")
            ->setColumns(2)
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setDecimals(4)
            ->getChamp()
        );
        
        //Is locale?
        $this->addChamp(
            (new JSChamp())
            ->createChoix("islocale", "Monnaie locale?")
            ->setColumns(2)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE)
            ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
