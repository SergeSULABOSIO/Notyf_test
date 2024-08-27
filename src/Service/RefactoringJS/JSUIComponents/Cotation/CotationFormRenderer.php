<?php

namespace App\Service\RefactoringJS\JSUIComponents\Cotation;

use App\Entity\Cotation;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
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
        //Documents
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
        //Chargements
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
        //Prime totale
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
                ->createSection("Détails relatifs aux termes ou mode de paiement.")
                ->setIcon("fa-solid fa-cash-register")
                ->setColumns(10)
                ->getChamp()
        );
        //Tranches
        $this->addChamp(
            (new JSChamp())
                ->createCollection('tranches', PreferenceCrudController::PREF_CRM_COTATION_TRANCHES)
                ->setHelp("Vous avez la possibilité d'ajouter des données à volonté.")
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setRequired(false)
                ->setColumns(10)
                ->getChamp()
        );
        //Section - Commissions
        $this->addChamp(
            (new JSChamp())
                ->createSection("Détails relatifs à la commission de courtage")
                ->setIcon("fa-solid fa-cash-register")
                ->setColumns(10)
                ->getChamp()
        );
        //Revenu
        $this->addChamp(
            (new JSChamp())
                ->createCollection('revenus', PreferenceCrudController::PREF_CRM_COTATION_REVENUS)
                ->setHelp("Vous avez la possibilité d'ajouter des données à volonté.")
                ->useEntryCrudForm(RevenuCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setRequired(false)
                ->setColumns(10)
                ->getChamp()
        );
        //Revenu pure total
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuPureTotal', "Revenu pure")
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuPureTotal())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Taxe courtie totale
        $this->addChamp(
            (new JSChamp())
                ->createArgent('taxeCourtierTotale', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxArca * 100) . "%)"))
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeCourtierTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Revenu net total
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuNetTotal', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur() . " (net)")
                ->setDisabled(true)
                ->setHelp("La partie partageable + la partie non partageable.")
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuNetTotal())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Taxe assureur totale
        $this->addChamp(
            (new JSChamp())
                ->createArgent('taxeAssureurTotale', ucfirst($this->serviceTaxes->getNomTaxeAssureur() . " (" . ($tauxTva * 100) . "%)"))
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeAssureurTotale())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Revenu total ttc
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuTotalTTC', "Revenu TTC")
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuTotalTTC())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );

        //Section - Rétrocommissions
        $this->addChamp(
            (new JSChamp())
                ->createSection("Détails relatifs à la rétrocommission dûe au partenaire")
                ->setIcon("fas fa-handshake")
                ->setColumns(10)
                ->getChamp()
        );
        //Revenu Net total partageable
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuNetTotalPartageable', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
                ->setHelp("Uniquement la partie partageable avec le partenaire.")
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuNetTotalPartageable())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Taxe courtier total partageable
        $this->addChamp(
            (new JSChamp())
                ->createArgent('taxeCourtierTotalePartageable', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxArca * 100) . "%)"))
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTaxeCourtierTotalePartageable())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Revenu net total partageable
        $this->addChamp(
            (new JSChamp())
                ->createArgent('revenuNetTotalPartageable', "Revenu net partageable")
                ->setHelp("La partie du revenu net qui est parteageable avec le partenaire ou encore l'assiette.")
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRevenuNetTotalPartageable())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createTexte('partenaire', "Partenaire")
                ->setDisabled(true)
                ->setColumns(10)
                ->getChamp()
        );
        //Pourcentage de retrocommission du partenaire
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage('tauxretrocompartenaire', PreferenceCrudController::PREF_CRM_COTATION_TAUX_RETROCOM)
                ->setHelp("Ne définissez rien si vous voullez appliquer le taux par défaut.")
                ->setDisabled(true)
                ->setColumns(10)
                ->getChamp()
        );
        //Retrocom Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createArgent('retroComPartenaire', "Rétrocommission")
                ->setHelp("Le montant total dû au partenaire.")
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getRetroComPartenaire())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->setColumns(10)
                ->getChamp()
        );
        //Réserve
        $this->addChamp(
            (new JSChamp())
                ->createArgent('reserve', "Réserve dû au courtier lui-même")
                ->setDisabled(true)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Cotation $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getReserve())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
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
