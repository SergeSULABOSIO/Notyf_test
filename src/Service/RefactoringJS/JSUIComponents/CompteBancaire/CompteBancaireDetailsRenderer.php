<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Entity\Tranche;
use App\Service\ServiceTaxes;
use App\Entity\CompteBancaire;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\PaiementCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class CompteBancaireDetailsRenderer extends JSPanelRenderer
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
        //Intitulé du compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte("intitule", "Intitulé")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Numéro du compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte("numero", "Numéro du compte")
                ->setColumns(6)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Banque
        $this->addChamp(
            (new JSChamp())
                ->createTexte("banque", "Banque")
                ->setColumns(3)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Code Swift
        $this->addChamp(
            (new JSChamp())
                ->createTexte("codeSwift", "Code Swift")
                ->setColumns(3)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Code monnaie
        $this->addChamp(
            (new JSChamp())
                ->createTexte("codeMonnaie", "Devise")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Date de création
        $this->addChamp(
            (new JSChamp())
                ->createDate("createdAt", "D. Création")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Dernière modification
        $this->addChamp(
            (new JSChamp())
                ->createDate("updatedAt", "D. Modification")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("utilisateur", "Utilisateur")
                ->setPermission(
                    UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
                )
                ->setColumns(10)
                ->getChamp()
        );
        
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", "Entreprise")
                ->setPermission(
                    UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE],
                )
                ->setColumns(10)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
