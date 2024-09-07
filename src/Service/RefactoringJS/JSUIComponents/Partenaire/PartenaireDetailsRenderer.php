<?php

namespace App\Service\RefactoringJS\JSUIComponents\Partenaire;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Partenaire;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PartenaireDetailsRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_DETAILS, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Id
        $this->addChamp(
            (new JSChamp())
                ->createNombre("id", PreferenceCrudController::PREF_PRO_PARTENAIRE_ID, 0)
                ->setColumns(10)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)
                ->setColumns(10)
                ->getChamp()
        );
        //Part
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)
                ->setColumns(10)
                ->getChamp()
        );
        //Adresse
        $this->addChamp(
            (new JSChamp())
                ->createTexte('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)
                ->setColumns(10)
                ->getChamp()
        );
        //Email
        $this->addChamp(
            (new JSChamp())
                ->createTexte('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)
                ->setColumns(10)
                ->getChamp()
        );
        //Site web
        $this->addChamp(
            (new JSChamp())
                ->createTexte('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)
                ->setColumns(10)
                ->getChamp()
        );
        //RCCM
        $this->addChamp(
            (new JSChamp())
                ->createTexte('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)
                ->setColumns(10)
                ->getChamp()
        );
        //Idnat
        $this->addChamp(
            (new JSChamp())
                ->createTexte('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)
                ->setColumns(10)
                ->getChamp()
        );
        //Num Impot
        $this->addChamp(
            (new JSChamp())
                ->createTexte('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)
                ->setColumns(10)
                ->getChamp()
        );
        //Piste
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('pistes', "Pistes")
                ->setTemplatePath('admin/segment/view_pistes.html.twig')
                ->setColumns(10)
                ->getChamp()
        );
        //Polices
        $this->addChamp(
            (new JSChamp())
                ->createTableau('polices', "Polices")
                ->setTemplatePath('admin/segment/view_polices.html.twig')
                ->setColumns(10)
                ->getChamp()
        );
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("utilisateur", PreferenceCrudController::PREF_PRO_PARTENAIRE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setColumns(10)
                ->getChamp()
        );
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", PreferenceCrudController::PREF_PRO_PARTENAIRE_ENTREPRISE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setColumns(10)
                ->getChamp()
        );
        //Date de création
        $this->addChamp(
            (new JSChamp())
                ->createDate("createdAt", PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_CREATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Partenaire $objet) {
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
                ->createDate("updatedAt", PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Partenaire $objet) {
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
