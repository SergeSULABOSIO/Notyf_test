<?php

namespace App\Service\RefactoringJS\JSUIComponents\Police;

use App\Entity\Piste;
use App\Entity\Police;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PoliceFormRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    private function isIard(): bool
    {
        $rep = false;
        if ($this->adminUrlGenerator->get("isIard")) {
            $rep = $this->adminUrlGenerator->get("isIard");
        }
        return $rep;
    }

    public function isNewPiste()
    {
        $isNewPiste = true;
        if ($this->objetInstance instanceof Piste) {
            if ($this->objetInstance->getId()) {
                $isNewPiste = false;
            }
        }
        return $isNewPiste;
    }

    public function design()
    {
        $tauxAssureur = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), false);
        $tauxCourtier = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);

        $column = 12;
        if ($this->objetInstance instanceof Police) {
            $column = 10;
        }
        //**************************************************************** */
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-file-shield') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Le contrat d'assurance en place.")
                ->setColumns($column)
                ->getChamp()
        );
        //Cotation
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)
                ->setColumns($column)
                ->setRequired(false)
                ->setFormTypeOptions('query_builder', function (EntityRepository $entityRepository) {
                    if ($this->objetInstance instanceof Piste) {
                        /** @var Piste */
                        $piste = $this->objetInstance;
                        if ($this->isNewPiste() == false) {
                            return $entityRepository
                                ->createQueryBuilder('e')
                                ->Where('e.entreprise = :ese')
                                ->andWhere('e.piste = :piste')
                                ->setParameter('piste', $piste)
                                ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                        } else {
                            return $entityRepository
                                ->createQueryBuilder('e')
                                ->Where('e.entreprise = :ese')
                                ->andWhere('e.validated = :val')
                                ->andWhere('e.piste = :piste')
                                ->setParameter('val', 0)
                                ->setParameter('piste', null)
                                ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                        }
                    } else {
                        return $entityRepository
                            ->createQueryBuilder('e')
                            ->Where('e.entreprise = :ese')
                            ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                    }
                })
                ->getChamp()
        );
        //Reference
        $this->addChamp(
            (new JSChamp())
                ->createTexte('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
                ->setColumns($column)
                ->getChamp()
        );
        //Date opération
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)
                ->setColumns($column)
                ->getChamp()
        );
        //Date émission
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)
                ->setColumns($column)
                ->getChamp()
        );
        //Date effet
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
                ->setColumns($column)
                ->getChamp()
        );

        if ($this->isNewPiste() == false) {
            /** @var Piste */
            $police = $this->objetInstance;
            if (count($police->getPolices()) != 0) {
                //Date expiration
                $this->addChamp(
                    (new JSChamp())
                        ->createDate('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Id Avenant
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('idAvenant', PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Type Avenant
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Produit
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('produit', "Couverture")
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Client
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('client', "Client")
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Gestionnaire
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //Assistant Gestionnaire
                $this->addChamp(
                    (new JSChamp())
                        ->createTexte('assistant', PreferenceCrudController::PREF_PRO_POLICE_ASSISTANT)
                        ->setColumns($column)
                        ->setDisabled(true)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Document
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Documents ou pièces jointes")
                        ->setIcon("fa-solid fa-paperclip")
                        ->getChamp()
                );
                //Document
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
                        ->useEntryCrudForm(DocPieceCrudController::class)
                        ->allowAdd(true)
                        ->allowDelete(true)
                        ->setRequired(false)
                        ->setColumns($column)
                        ->getChamp()
                );
                //****************************************************** */
                //Section - Contact
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Contacts")
                        ->setIcon("fas fa-address-book")
                        ->setHelp("Personnes impliquées dans les échanges.")
                        ->getChamp()
                );
                //Contact
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('contacts', "Contacts")
                        ->setRequired(true)
                        ->setColumns($column)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Primes d'assurance
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Prime d'assurance")
                        ->setIcon("fa-solid fa-cash-register")
                        ->setHelp("Structure de la prime d'assurance résultant de la mise en place de l'avenant.")
                        ->getChamp()
                );
                //Chargements
                $this->addChamp(
                    (new JSChamp())
                        ->createCollection('chargements', "Structure")
                        ->setRequired(true)
                        ->setColumns($column)
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
                        ->setDisabled(true)
                        ->getChamp()
                );
                //******************************************************* */
                //Section - Termes de paiement
                $this->addChamp(
                    (new JSChamp())
                        ->createSection("Termes de paiement de la prime")
                        ->setIcon("fa-solid fa-cash-register")
                        ->setHelp("La manière dont la prime d'assurance devra être versée par le client.")
                        ->getChamp()
                );
            }
        }























        $taux = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);







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
        //Revenu totale ht partageable
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuTotalHTPartageable', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
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
        //Taxe courtier total partageable
        $this->addChamp(
            (new JSChamp())
                ->createArgent('taxeCourtierTotalePartageable', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($taux * 100) . "%)"))
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
        //Revenu net total partageable
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuNetTotalPartageable', "Revenu net partageable")
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
        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createTexte('partenaire', "Partenaire")
                ->setColumns(10)
                ->getChamp()
        );
        //Taux retrocommission partenaire
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage('tauxretrocompartenaire', "Taux exceptionnel")
                ->setColumns(10)
                ->getChamp()
        );
        //Retrocommission partenaire
        $this->addChamp(
            (new JSChamp())
                ->createArgent('retroComPartenaire', "Rétrocommission")
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
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
