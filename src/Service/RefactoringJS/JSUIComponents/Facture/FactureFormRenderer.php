<?php

namespace App\Service\RefactoringJS\JSUIComponents\Facture;

use App\Entity\Facture;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ElementFactureCrudController;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;
use Doctrine\ORM\Query\AST\NewObjectExpression;

class FactureFormRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Facture) {
            $column = 10;
        }

        //Onglet Article
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(" Articles facturés")
                ->setIcon("fas fa-handshake")
                ->setHelp("Les articles de la facture.")
                ->getChamp()
        );

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fa-solid fa-receipt') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Facture / Note de débit / Note de percetion pertant de collecter des fonds. Ceci n'est qu'une représentation virtuelle de la copie réelle que vous devez attacher ici dès que possible.")
                ->setColumns($column)
                ->getChamp()
        );

        //Montant TTC
        $this->addChamp(
            (new JSChamp())
                ->createArgent("totalSolde", "Solde à payer")
                ->setDisabled(true)
                ->setColumns($column)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Facture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getTotalSolde())))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Panel Articles facturées
        $this->addChamp(
            (new JSChamp())
                ->createSection("Articles facturés")
                ->setIcon("fa-solid fa-layer-group")
                ->setHelp("Elements constitutifs de la facture ou de la note de débit/crédit.")
                ->setColumns($column)
                ->getChamp()
        );

        //Elements facturés
        $this->addChamp(
            (new JSChamp())
                ->createCollection("elementFactures", "Eléments facturés")
                ->setColumns($column)
                ->useEntryCrudForm(ElementFactureCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->getChamp()
        );

        //Onglet Informations générales
        $this->addChamp(
            (new JSChamp())
                ->createOnglet(" Autres générales")
                ->setColumns($column)
                ->setIcon("fas fa-handshake")
                ->setHelp("Les articles de la facture.")
                ->getChamp()
        );

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Section principale")
                ->setIcon("fas fa-location-crosshairs")
                ->setColumns($column)
                ->getChamp()
        );

        //Destination
        $this->addChamp(
            (new JSChamp())
                ->createChoix("destination", "Destination")
                ->setColumns($column)
                ->setChoices(FactureCrudController::TAB_DESTINATION)
                ->getChamp()
        );

        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("assureur", "Assureur")
                ->setColumns(5)
                ->getChamp()
        );

        //Partenaire
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("partenaire", "Partenaire")
                ->setColumns(5)
                ->getChamp()
        );

        //Autres tiers
        $this->addChamp(
            (new JSChamp())
                ->createTexte("autreTiers", "Tiers Concerné")
                ->setColumns(5)
                ->getChamp()
        );

        //Rférence de la facture
        $this->addChamp(
            (new JSChamp())
                ->createTexte("reference", "Référence")
                ->setColumns($column)
                ->setFormatValue(
                    function ($value, Facture $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        // Ici, voir en bas, c'est à partir de là qu'il faut continuer en se basant sur le modèle d'en haut.
        //Description
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte("description", "Description")
                ->setColumns($column)
                ->getChamp()
        );

        //Comptes Bancaires
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("compteBancaires", "Comptes bancaires")
                ->setColumns($column)
                ->getChamp()
        );

        //Signed By
        $this->addChamp(
            (new JSChamp())
                ->createTexte("signedBy", "Signé par")
                ->setRequired(true)
                ->setColumns(3)
                ->getChamp()

        );

        //Poste Signed by
        $this->addChamp(
            (new JSChamp())
                ->createTexte("posteSignedBy", "Fonction")
                ->setRequired(true)
                ->setColumns(3)
                ->getChamp()
        );

        //Onglet Documents
        $this->addChamp(
            (new JSChamp())
                ->createOnglet("Documents ou pièces jointes")
                ->setColumns($column)
                ->setIcon("fa-solid fa-paperclip")
                ->setHelp("Merci d'attacher vos pièces justificatives par ici.")
                ->getChamp()
        );

        //Documents
        $this->addChamp(
            (new JSChamp())
                ->createCollection("documents", "Documents")
                ->setColumns($column)
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        /**
         * L'objectif de ce traitement de masse c'est de pouvoir ne pas afficher certains champs
         * du formulaire en fonction du type de facture que l'on est en train
         * de payer.
         * Le comportement du formulaire doit varier en fonction du type de facture que l'on paie.
         */
        // dd($type);
        $destination = null;
        // dd($adminUrlGenerator->get("donnees"));
        if ($adminUrlGenerator->get("donnees") != null) {
            $destination = $adminUrlGenerator->get("donnees")["destination"];
        } else if ($objetInstance != null) {
            if ($objetInstance instanceof Facture) {
                foreach (FactureCrudController::TAB_DESTINATION as $nomDestination => $codeDestination) {
                    /** @var Facture  */
                    if ($codeDestination == $objetInstance->getDestination()) {
                        // dd($codeType, $nomType, $objetInstance->getType());
                        $destination = $nomDestination;
                    }
                }
                // dd($objetInstance);
            }
        }
        // dd("Ici", $type);
        if (FactureCrudController::DESTINATION_DGI == $destination || FactureCrudController::DESTINATION_ARCA == $destination) {
            $this->addChampToDeactivate("destination", 4);
            $this->addChampToDeactivate("autreTiers", 3);
            $this->addChampToDeactivate("reference", 3);
            $this->addChampToRemove("type");
            $this->addChampToRemove("partenaire");
            $this->addChampToRemove("compteBancaires");
            $this->addChampToRemove("assureur");
        } else if (FactureCrudController::DESTINATION_ASSUREUR == $destination) {
            $this->addChampToDeactivate("destination", 3);
            $this->addChampToDeactivate("assureur", 3);
            $this->addChampToDeactivate("reference", 4);
            $this->addChampToRemove("compteBancaires");
            $this->addChampToRemove("type");
            $this->addChampToRemove("autreTiers");
            $this->addChampToRemove("partenaire");
        } else if (FactureCrudController::DESTINATION_CLIENT == $destination) {
            $this->addChampToDeactivate("destination", 3);
            $this->addChampToDeactivate("autreTiers", 3);
            $this->addChampToDeactivate("reference", 4);
            $this->addChampToRemove("compteBancaires");
            $this->addChampToRemove("type");
            $this->addChampToRemove("partenaire");
            $this->addChampToRemove("assureur");
        } else if (FactureCrudController::DESTINATION_PARTENAIRE == $destination) {
            $this->addChampToDeactivate("destination", 3);
            $this->addChampToDeactivate("partenaire", 3);
            $this->addChampToDeactivate("reference", 4);
            $this->addChampToRemove("compteBancaires");
            $this->addChampToRemove("type");
            $this->addChampToRemove("assureur");
            $this->addChampToRemove("autreTiers");
        }
        return $champs;
    }
}
