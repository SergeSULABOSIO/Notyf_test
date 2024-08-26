<?php

namespace App\Service\RefactoringJS\JSUIComponents\Cotation;

use App\Entity\Cotation;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Controller\Admin\CotationCrudController;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class CotationFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private ServiceEntreprise $serviceEntreprise,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_DETAILS, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    private function isIard(): bool
    {
        $rep = false;
        if ($this->adminUrlGenerator->get("isIard")) {
            $rep = $this->adminUrlGenerator->get("isIard");
        }
        return $rep;
    }

    public function design()
    {
        $tauxArca = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);
        $tauxTva = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), false);

        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_CRM_COTATION_NOM)
                ->setRequired(false)
                ->setColumns(10)
                ->getChamp()
        );
        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)
                ->setRequired(false)
                ->setColumns(10)
                ->setFormTypeOption(
                    function (EntityRepository $entityRepository) {
                        return $entityRepository
                            ->createQueryBuilder('e')
                            ->Where('e.entreprise = :ese')
                            ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                    }
                )
                ->getChamp()
        );
        //validated
        $this->addChamp(
            (new JSChamp())
                ->createBoolean('validated', PreferenceCrudController::PREF_CRM_COTATION_RESULTAT)
                ->setColumns(10)
                ->renderAsSwitch(false)
                ->setRequired(true)
                ->setDisabled(true)
                ->getChamp()
        );
        //Durée de la couverture
        $this->addChamp(
            (new JSChamp())
                ->createNombre("dureeCouverture", PreferenceCrudController::PREF_CRM_COTATION_DUREE)
                ->setColumns(10)
                ->getChamp()
        );
        //Section - Documents
        $this->addChamp(
            (new JSChamp())
                ->createSection("Documents ou pièces jointes")
                ->setIcon("fa-solid fa-paperclip")
                ->setColumns(10)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createCollection('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setRequired(false)
                ->setColumns(10)
                ->getChamp()
        );
        //Section - Chargements sur prime d'assurance
        $this->addChamp(
            (new JSChamp())
                ->createSection("Détails relatifs à la prime d'assurance")
                ->setIcon("fa-solid fa-cash-register")
                ->setColumns(10)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createCollection('chargements', PreferenceCrudController::PREF_CRM_COTATION_CHARGEMENT)
                ->setHelp("Vous avez la possibilité d'ajouter des données à volonté.")
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setRequired(false)
                ->setColumns(10)
                ->getChamp()
        );
        $this->addChamp(
            (new JSChamp())
                ->createArgent("primeTotale", PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getPrimeTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Section - Termes de paiement
        $this->addChamp(
            (new JSChamp())
                ->createSection("Détails relatifs aux termes de paiement.")
                ->setIcon("fa-solid fa-cash-register")
                ->setColumns(10)
                ->getChamp()
        );










        // //validated
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTexte("validated", "Status")
        //         ->setColumns(10)
        //         ->setChoices(CotationCrudController::TAB_TYPE_RESULTAT)
        //         ->getChamp()
        // );
        // //polices
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTableau("polices", "Polices")
        //         ->setTemplatePath('admin/segment/view_polices.html.twig')
        //         ->getChamp()
        // );
        // //Nom
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTexte("nom", PreferenceCrudController::PREF_CRM_COTATION_NOM)
        //         ->setColumns(10)
        //         ->getChamp()
        // );
        // //Durée de la couverture
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createNombre("dureeCouverture", PreferenceCrudController::PREF_CRM_COTATION_DUREE)
        //         ->setColumns(10)
        //         ->setFormatValue(
        //             function ($value, Cotation $entity) {
        //                 return $value . " mois.";
        //             }
        //         )
        //         ->getChamp()
        // );
        // //Client
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createAssociation('client', "Client")
        //         ->setColumns(10)
        //         ->getChamp()
        // );
        // //Assureur
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createAssociation('assureur', "Assureur")
        //         ->setColumns(10)
        //         ->getChamp()
        // );
        // //Piste
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createAssociation('piste', "Piste")
        //         ->setColumns(10)
        //         ->getChamp()
        // );
        // //Partenaire
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createAssociation('partenaire', "Partenaire")
        //         ->setColumns(10)
        //         ->getChamp()
        // );
        // //Revenus
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTableau('revenus', "Revenus de courtage")
        //         ->setTemplatePath('admin/segment/view_revenus.html.twig')
        //         ->getChamp()
        // );
        // //Chargements
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTableau('chargements', "Chargement")
        //         ->setTemplatePath('admin/segment/view_chargements.html.twig')
        //         ->getChamp()
        // );
        // //Prime Totale
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("primeTotale", PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
        //         ->setColumns(10)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, Cotation $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getPrimeTotale())))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );
        // //Tranches
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTableau('tranches', PreferenceCrudController::PREF_CRM_COTATION_TRANCHES)
        //         ->setTemplatePath('admin/segment/view_tranches.html.twig')
        //         ->getChamp()
        // );
        // //Documents
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTableau('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
        //         ->setTemplatePath('admin/segment/view_documents.html.twig')
        //         ->getChamp()
        // );
        // //Gestionnaire
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTexte('gestionnaire', "Gestionnaire")
        //         ->setColumns(10)
        //         ->getChamp()
        // );
        // //Utilisateur
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createAssociation("utilisateur", "Utilisateur")
        //         ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
        //         ->setColumns(10)
        //         ->getChamp()
        // );
        // //Date de création
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createDate('createdAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION)
        //         ->setColumns(10)
        //         ->setFormatValue(
        //             function ($value, Cotation $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );
        // //Dernière modification
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createDate('updatedAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION)
        //         ->setColumns(10)
        //         ->setFormatValue(
        //             function ($value, Cotation $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );
        // //Entreprise
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createAssociation('entreprise', PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE)
        //         ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
        //         ->setColumns(10)
        //         ->getChamp()
        // );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
