<?php

namespace App\Service\RefactoringJS\JSUIComponents\Produit;

use App\Entity\Partenaire;
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

class ProduitListeRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_LISTE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Code
        $this->addChamp(
            (new JSChamp())
                ->createTexte('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
                ->setColumns(10)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
                ->setColumns(10)
                ->getChamp()
        );
        //IARD?
        $this->addChamp(
            (new JSChamp())
                ->createChoix('iard', PreferenceCrudController::PREF_PRO_PRODUIT_IARD)
                ->setChoices(["IARD (Non Vie)" => 1, "VIE" => 0])
                ->setColumns(10)
                ->getChamp()
        );
        //Obligatoire?
        $this->addChamp(
            (new JSChamp())
                ->createChoix('obligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
                ->setColumns(10)
                ->getChamp()
        );
        //Abonnement?
        $this->addChamp(
            (new JSChamp())
                ->createChoix('abonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
                ->setColumns(10)
                ->getChamp()
        );
        //Description
        $this->addChamp(
            (new JSChamp())
                ->createZonneTexte('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
                ->setColumns(10)
                ->getChamp()
        );
        //Part
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
                ->setColumns(10)
                ->getChamp()
        );
        //Dernière modification
        $this->addChamp(
            (new JSChamp())
                ->createDate("updatedAt", PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Produit $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
