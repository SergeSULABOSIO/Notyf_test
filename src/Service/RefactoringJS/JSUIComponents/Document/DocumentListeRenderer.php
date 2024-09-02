<?php

namespace App\Service\RefactoringJS\JSUIComponents\Document;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\DocPiece;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class DocumentListeRenderer extends JSPanelRenderer
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
        //Logo
        $this->addChamp(
            (new JSChamp())
                ->createTexte('logoFormatFichier', "Format")
                ->setColumns(10)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_BIB_DOCUMENT_NOM)
                ->setColumns(10)
                ->getChamp()
        );
        //Nom Type
        $this->addChamp(
            (new JSChamp())
                ->createTexte('nomType', PreferenceCrudController::PREF_BIB_DOCUMENT_TYPE)
                ->setColumns(10)
                ->getChamp()
        );
        //Piste
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('piste', "Piste")
                ->setColumns(10)
                ->getChamp()
        );
        //Cotation
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('cotation', "Proposition")
                ->setColumns(10)
                ->getChamp()
        );
        //Piste
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('police', "Police")
                ->setColumns(10)
                ->getChamp()
        );
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("utilisateur", PreferenceCrudController::PREF_BIB_DOCUMENT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setColumns(10)
                ->getChamp()
        );
        //Date de création
        $this->addChamp(
            (new JSChamp())
                ->createDate("createdAt", PreferenceCrudController::PREF_BIB_DOCUMENT_DATE_DE_CREATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, DocPiece $objet) {
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
