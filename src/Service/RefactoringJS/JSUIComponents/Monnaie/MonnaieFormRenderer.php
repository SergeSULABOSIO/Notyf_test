<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Monnaie;
use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

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
        $this->addChampChoix(
            null,
            "code",
            "Code",
            false,
            false,
            6,
            MonnaieCrudController::TAB_MONNAIES,
            null
        );
        //Fonction
        $this->addChampChoix(
            null,
            "fonction",
            "Fonction Système",
            false,
            false,
            2,
            MonnaieCrudController::TAB_MONNAIE_FONCTIONS,
            null
        );
        //Taux en USD
        $this->addChampArgent(
            null,
            "tauxusd",
            "Taux (en USD)",
            false,
            false,
            2,
            $this->serviceMonnaie->getCodeAffichage(),
            function ($value, Monnaie $objet) {
                /** @var JSCssHtmlDecoration */
                $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                    ->ajouterClasseCss($this->css_class_bage_ordinaire)
                    ->outputHtml();
                return $formatedHtml;
            },
            4
        );
        //Is locale?
        $this->addChampChoix(
            null,
            "islocale",
            "Monnaie locale?",
            false,
            false,
            2,
            MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE,
            null
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
