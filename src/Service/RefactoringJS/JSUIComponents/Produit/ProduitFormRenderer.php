<?php

namespace App\Service\RefactoringJS\JSUIComponents\Produit;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\ProduitCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class ProduitFormRenderer extends JSPanelRenderer
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
        if ($this->objetInstance instanceof Produit) {
            $column = 10;
        }
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-gifts') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Une couverture d'assurance.")
                ->setColumns($column)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //Code
        $this->addChamp(
            (new JSChamp())
                ->createTexte('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
                ->setColumns($column)
                ->getChamp()
        );
        //Part
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
                ->setColumns($column)
                ->getChamp()
        );
        //Obligatoire?
        $this->addChamp(
            (new JSChamp())
                ->createBoolean('obligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
                // ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
                ->setColumns($column)
                ->getChamp()
        );
        //Abonnement?
        $this->addChamp(
            (new JSChamp())
                ->createBoolean('abonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
                // ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
                ->setColumns($column)
                ->getChamp()
        );
        //IARD?
        $this->addChamp(
            (new JSChamp())
                ->createChoix('iard', PreferenceCrudController::PREF_PRO_PRODUIT_IARD)
                ->setChoices(["IARD (Non Vie)" => 1, "VIE" => 0])
                ->setColumns($column)
                ->getChamp()
        );
        //Description
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
