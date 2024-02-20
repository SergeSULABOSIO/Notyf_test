<?php

namespace App\Service\RefactoringJS\JSUIComponents\Monnaie;

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
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class MonnaieDetailsRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_DETAILS, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", "Intitulé")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Monnaie $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Code
        $this->addChamp(
            (new JSChamp())
                ->createTexte("code", "Code")
                ->setColumns(6)
                ->setRequired(false)
                ->setDisabled(false)
                ->setFormatValue(
                    function ($value, Monnaie $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
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
                ->setFormatValue(
                    function ($value, Monnaie $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
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
        
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("utilisateur", "Utilisateur")
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );
        
        //Date creation
        $this->addChamp(
            (new JSChamp())
                ->createDate("createdAt", "D. Création")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Monnaie $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Date modification
        $this->addChamp(
            (new JSChamp())
                ->createDate("updatedAt", "D. Modification")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Monnaie $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", "Entreprise")
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
