<?php

namespace App\Service\RefactoringJS\JSUIComponents\Sinistre;

use App\Entity\Sinistre;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceCrossCanal;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Controller\Admin\EtapeSinistreCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class SinistreDetailsRenderer extends JSPanelRenderer
{
    //SINISTRE
    public $total_sinistre_cout = 0;
    public $total_sinistre_indemnisation = 0;
    public $total_piste_caff_esperes = 0;

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

    public function setTitreReportingSinistre(Sinistre $sinistre)
    {
        //dd($this->adminUrlGenerator->get("codeReporting"));
        if ($this->adminUrlGenerator->get("codeReporting") != null) {
            //SINISTRE
            if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_SINISTRE_TOUS) {
                $this->total_sinistre_cout += $sinistre->getCout();
                $this->total_sinistre_indemnisation += $sinistre->getMontantPaye();

                if ($this->crud) {
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " \n
                    [
                        Dégâts estimés: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_sinistre_cout) . ", 
                        Compensation versée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_sinistre_indemnisation) . "
                    ]");
                }
            }
        }
    }

    public function design()
    {
        //Id
        $this->addChamp(
            (new JSChamp())
                ->createNombre("id", PreferenceCrudController::PREF_SIN_SINISTRE_ID)
                ->setColumns(10)
                ->getChamp()
        );
        //Titre
        $this->addChamp(
            (new JSChamp())
                ->createTexte('titre', PreferenceCrudController::PREF_SIN_SINISTRE_ITITRE)
                ->setColumns(10)
                ->getChamp()
        );
        //Référence
        $this->addChamp(
            (new JSChamp())
                ->createTexte('numero', PreferenceCrudController::PREF_SIN_SINISTRE_REFERENCE)
                ->setFormatValue(function ($value, Sinistre $sinistre) {
                    $this->setTitreReportingSinistre($sinistre);
                    return $value;
                })
                ->setColumns(10)
                ->getChamp()
        );
        //Etape
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('etape', PreferenceCrudController::PREF_SIN_SINISTRE_ETAPE)
                ->setColumns(10)
                ->getChamp()
        );
        //Victimes
        $this->addChamp(
            (new JSChamp())
                ->createTableau('victimes', PreferenceCrudController::PREF_SIN_SINISTRE_VICTIMES)
                ->setColumns(10)
                ->getChamp()
        );
        //Experts
        $this->addChamp(
            (new JSChamp())
                ->createTableau('experts', PreferenceCrudController::PREF_SIN_SINISTRE_EXPERT)
                ->setColumns(10)
                ->getChamp()
        );
        //Documents
        $this->addChamp(
            (new JSChamp())
                ->createTableau('docPieces', PreferenceCrudController::PREF_SIN_SINISTRE_DOCUMENTS)
                ->setColumns(10)
                ->getChamp()
        );
        //Tâches
        $this->addChamp(
            (new JSChamp())
                ->createTableau('actionCRMs', PreferenceCrudController::PREF_SIN_SINISTRE_ACTIONS)
                ->setColumns(10)
                ->getChamp()
        );
        //Date d'occurrence
        $this->addChamp(
            (new JSChamp())
                ->createDate('occuredAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_OCCURENCE)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Sinistre $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Description
        $this->addChamp(
            (new JSChamp())
                ->createZonneTexte('description', PreferenceCrudController::PREF_SIN_SINISTRE_DESCRIPTION)
                ->setColumns(10)
                ->getChamp()
        );
        //Coût
        $this->addChamp(
            (new JSChamp())
                ->createArgent('cout', PreferenceCrudController::PREF_SIN_SINISTRE_COUT)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Sinistre $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getCout())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Montant payé
        $this->addChamp(
            (new JSChamp())
                ->createArgent('montantPaye', PreferenceCrudController::PREF_SIN_SINISTRE_MONTANT_PAYE)
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Sinistre $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontantPaye())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Date de paiement / d'indemnisation
        $this->addChamp(
            (new JSChamp())
                ->createDate('paidAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_PAIEMENT)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Sinistre $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Police
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('police', PreferenceCrudController::PREF_SIN_SINISTRE_POLICE)
                ->setColumns(10)
                ->getChamp()
        );
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('utilisateur', PreferenceCrudController::PREF_SIN_SINISTRE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setColumns(10)
                ->getChamp()
        );
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('entreprise', PreferenceCrudController::PREF_SIN_SINISTRE_ENTREPRISE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setColumns(10)
                ->getChamp()
        );
        //Dernière modification
        $this->addChamp(
            (new JSChamp())
                ->createDate('updatedAt', PreferenceCrudController::PREF_SIN_SINISTRE_DERNIRE_MODIFICATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Sinistre $objet) {
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
                ->createDate('createdAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_DE_CREATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Sinistre $objet) {
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
