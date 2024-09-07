<?php

namespace App\Service\RefactoringJS\JSUIComponents\Client;

use App\Entity\Assureur;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\ClientCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class ClientDetailsRenderer extends JSPanelRenderer
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
                ->createNombre("id", PreferenceCrudController::PREF_PRO_CLIENT_ID, 0)
                ->setColumns(10)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_PRO_CLIENT_NOM)
                ->setColumns(10)
                ->getChamp()
        );
        //Adresse
        $this->addChamp(
            (new JSChamp())
                ->createTexte('adresse', PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE)
                ->setColumns(10)
                ->getChamp()
        );
        //Téléphone
        $this->addChamp(
            (new JSChamp())
                ->createTexte('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)
                ->setColumns(10)
                ->getChamp()
        );
        //Email
        $this->addChamp(
            (new JSChamp())
                ->createTexte('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)
                ->setColumns(10)
                ->getChamp()
        );
        //Site web
        $this->addChamp(
            (new JSChamp())
                ->createTexte('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)
                ->setColumns(10)
                ->getChamp()
        );
        //Personne morale?
        $this->addChamp(
            (new JSChamp())
                ->createChoix('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
                ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE)
                ->setColumns(10)
                ->getChamp()
        );
        //RCCM
        $this->addChamp(
            (new JSChamp())
                ->createTexte('rccm', PreferenceCrudController::PREF_PRO_CLIENT_RCCM)
                ->setColumns(10)
                ->getChamp()
        );
        //Idnat
        $this->addChamp(
            (new JSChamp())
                ->createTexte('idnat', PreferenceCrudController::PREF_PRO_CLIENT_IDNAT)
                ->setColumns(10)
                ->getChamp()
        );
        //Num Impot
        $this->addChamp(
            (new JSChamp())
                ->createTexte('numipot', PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT)
                ->setColumns(10)
                ->getChamp()
        );
        //Secteur
        $this->addChamp(
            (new JSChamp())
                ->createChoix('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
                ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR)
                ->setColumns(10)
                ->getChamp()
        );
        //Cotation
        $this->addChamp(
            (new JSChamp())
                ->createTableau("cotations", "Proposition")
                ->setTemplatePath('admin/segment/view_cotations.html.twig')
                ->setColumns(10)
                ->getChamp()
        );
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("utilisateur", PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setColumns(10)
                ->getChamp()
        );
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setColumns(10)
                ->getChamp()
        );
        //Date de création
        $this->addChamp(
            (new JSChamp())
                ->createDate("createdAt", PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Client $objet) {
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
                ->createDate("updatedAt", PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Client $objet) {
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
