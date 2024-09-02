<?php

namespace App\Service\RefactoringJS\JSUIComponents\Monnaie;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\MonnaieCrudController;
use App\Entity\Monnaie;
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
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Monnaie) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fa-solid fa-comment-dollar') //<i class="fa-solid fa-comment-dollar"></i>
                ->setHelp("Frais chargés au client par l'assureur, visibles sur la facture.")
                ->setColumns($column)
                ->getChamp()
        );

        //Code
        $this->addChamp(
            (new JSChamp())
                ->createChoix("code", "Code")
                ->setColumns($column)
                ->setChoices(MonnaieCrudController::TAB_MONNAIES)
                ->getChamp()
        );

        //Fonction
        $this->addChamp(
            (new JSChamp())
                ->createChoix("fonction", "Fonction Système")
                ->setColumns($column)
                ->setChoices(MonnaieCrudController::TAB_MONNAIE_FONCTIONS)
                ->getChamp()
        );

        //Taux en USD
        $this->addChamp(
            (new JSChamp())
                ->createArgent("tauxusd", "Taux (en USD)")
                ->setColumns($column)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setDecimals(4)
                ->getChamp()
        );

        //Is locale?
        $this->addChamp(
            (new JSChamp())
                ->createChoix("islocale", "Monnaie locale?")
                ->setColumns($column)
                ->setChoices(MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
