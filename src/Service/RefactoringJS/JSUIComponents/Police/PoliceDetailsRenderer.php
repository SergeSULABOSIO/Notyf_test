<?php

namespace App\Service\RefactoringJS\JSUIComponents\Police;

use App\Entity\Monnaie;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\MonnaieCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Police;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PoliceDetailsRenderer extends JSPanelRenderer
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
        $taux = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);
        //Id
        $this->addChamp(
            (new JSChamp())
                ->createNombre('id', PreferenceCrudController::PREF_PRO_POLICE_ID)
                ->setColumns(10)
                ->getChamp()
        );
        //Cotation
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)
                ->setColumns(10)
                ->getChamp()
        );
        //Reference
        $this->addChamp(
            (new JSChamp())
                ->createTexte('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
                ->setColumns(10)
                ->getChamp()
        );
        //Date opération
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Date émission
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Date effet
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Date expiration
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createTexte('assureur', "Assureur")
                ->setColumns(10)
                ->getChamp()
        );
        //Type Avenant
        $this->addChamp(
            (new JSChamp())
                ->createTexte('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
                ->setColumns(10)
                ->getChamp()
        );
        //Produit
        $this->addChamp(
            (new JSChamp())
                ->createTexte('produit', "Couverture")
                ->setTemplatePath('admin/segment/view_produit.html.twig')
                ->setColumns(10)
                ->getChamp()
        );
        //Client
        $this->addChamp(
            (new JSChamp())
                ->createTexte('client', "Assuré (client)")
                ->setColumns(10)
                ->getChamp()
        );
        //Gestionnaire de compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                ->setColumns(10)
                ->getChamp()
        );
        //Assistant Gestionnaire de compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte('assistant', PreferenceCrudController::PREF_PRO_POLICE_ASSISTANT)
                ->setColumns(10)
                ->getChamp()
        );
        //Utilisateur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('utilisateur', PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );
        //Date creation
        $this->addChamp(
            (new JSChamp())
                ->createDate('createdAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Police $objet) {
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
                ->createDate('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Police $objet) {
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
                ->createAssociation('entreprise', PreferenceCrudController::PREF_PRO_POLICE_ENTREPRISE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->getChamp()
        );

        //Panel contact
        $this->addChamp(
            (new JSChamp())
                ->createSection(" Détails relatifs aux Contacts")
                ->setIcon("fas fa-address-book")
                ->getChamp()
        );
        //Contacts
        $this->addChamp(
            (new JSChamp())
                ->createTableau('contacts', "Détails")
                ->setTemplatePath('admin/segment/view_contacts.html.twig')
                ->getChamp()
        );

        //Panel Documents
        $this->addChamp(
            (new JSChamp())
                ->createSection(" Documents")
                ->setIcon("fa-solid fa-paperclip")
                ->getChamp()
        );
        //Documents
        $this->addChamp(
            (new JSChamp())
                ->createTableau('documents', "Détails")
                ->setTemplatePath('admin/segment/view_documents.html.twig')
                ->getChamp()
        );

        //Panel structure de la prime
        $this->addChamp(
            (new JSChamp())
                ->createSection(" Détails relatifs à la prime d'assurance")
                ->setIcon("fa-solid fa-cash-register")
                ->getChamp()
        );
        //Chargements
        $this->addChamp(
            (new JSChamp())
                ->createTableau('chargements', "Détails")
                ->setTemplatePath('admin/segment/view_chargements.html.twig')
                ->getChamp()
        );
        //Prime totale
        $this->addChamp(
            (new JSChamp())
                ->createArgent('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Panel Termes de paiement
        $this->addChamp(
            (new JSChamp())
                ->createSection(" Détails relatifs aux termes de paiement")
                ->setIcon("fa-solid fa-cash-register")
                ->getChamp()
        );
        //Tranche
        $this->addChamp(
            (new JSChamp())
                ->createTableau('tranches', "Détails")
                ->setTemplatePath('admin/segment/view_tranches.html.twig')
                ->getChamp()
        );

        //Panel Revenu de courtage
        $this->addChamp(
            (new JSChamp())
                ->createSection(" Détails relatifs à la commission de courtage")
                ->setIcon("fa-solid fa-cash-register")
                ->getChamp()
        );
        //Revenu
        $this->addChamp(
            (new JSChamp())
                ->createTableau('revenus', "Détails")
                ->setTemplatePath('admin/segment/view_revenus.html.twig')
                ->getChamp()
        );
        //Commission totale ht
        $this->addChamp(
            (new JSChamp())
                ->createArgent('commissionTotaleHT', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Taxes courtiers totale
        $this->addChamp(
            (new JSChamp())
                ->createArgent('taxeCourtierTotale', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($taux * 100) . "%)"))
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Revenu net totale
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuNetTotal', "Revenu net total")
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Panel Retrocommission
        $this->addChamp(
            (new JSChamp())
                ->createSection(" Détails relatifs à la rétrocommission dûe au partenaire")
                ->setIcon("fas fa-handshake")
                ->getChamp()
        );














        // //Nom
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTexte("nom", "Intitulé")
        //         ->setRequired(false)
        //         ->setDisabled(false)
        //         ->setColumns(10)
        //         ->setFormatValue(
        //             function ($value, Monnaie $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Code
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createTexte("code", "Code")
        //         ->setColumns(6)
        //         ->setRequired(false)
        //         ->setDisabled(false)
        //         ->setFormatValue(
        //             function ($value, Monnaie $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );

        // //Fonction
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createChoix("fonction", "Fonction Système")
        //         ->setColumns(2)
        //         ->setChoices(MonnaieCrudController::TAB_MONNAIE_FONCTIONS)
        //         ->getChamp()
        // );

        // //Taux en USD
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createArgent("tauxusd", "Taux (en USD)")
        //         ->setColumns(2)
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setFormatValue(
        //             function ($value, Monnaie $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->setDecimals(4)
        //         ->getChamp()
        // );

        // //Is locale?
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createChoix("islocale", "Monnaie locale?")
        //         ->setColumns(2)
        //         ->setChoices(MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE)
        //         ->getChamp()
        // );

        // //Utilisateur
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createAssociation("utilisateur", "Utilisateur")
        //         ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
        //         ->getChamp()
        // );



        // //Date modification
        // $this->addChamp(
        //     (new JSChamp())
        //         ->createDate("updatedAt", "D. Modification")
        //         ->setColumns(10)
        //         ->setFormatValue(
        //             function ($value, Monnaie $objet) {
        //                 /** @var JSCssHtmlDecoration */
        //                 $formatedHtml = (new JSCssHtmlDecoration("span", $value))
        //                     ->ajouterClasseCss($this->css_class_bage_ordinaire)
        //                     ->outputHtml();
        //                 return $formatedHtml;
        //             }
        //         )
        //         ->getChamp()
        // );


    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
