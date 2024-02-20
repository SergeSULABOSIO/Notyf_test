<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiement;

use App\Entity\Paiement;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PaiementDetailsRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_DETAILS, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        //Paid at
        $this->addChamp(
            (new JSChamp())
                ->createDate("paidAt", "Date de paiement")
                ->setColumns(10)
                ->getChamp()
        );
        
        //Montant
        $this->addChamp(
            (new JSChamp())
                ->createArgent("montant", "Montant")
                ->setColumns(12)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Paiement $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getMontant() * 100)))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //description
        $this->addChamp(
            (new JSChamp())
                ->createTexte("description", "Description")
                ->setColumns(12)
                ->setFormatValue(
                    function ($value, Paiement $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Facture
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("facture", "Facture")
                ->setColumns(12)
                ->getChamp()
        );
        
        //Documents
        $this->addChamp(
            (new JSChamp())
                ->createTableau("documents", "Documents")
                ->setColumns(12)
                ->getChamp()
        );
        
        //Comptes Bancaires
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("compteBancaire", "Comptes bancaires")
                ->setColumns(12)
                ->getChamp()
        );
       
        //Created At
        $this->addChamp(
            (new JSChamp())
                ->createDate("createdAt", "D. Création")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Paiement $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        
        //Upadated At
        $this->addChamp(
            (new JSChamp())
                ->createDate("updatedAt", "Dernière modification")
                ->setColumns(10)
                ->setFormatValue(
                    function ($value, Paiement $objet) {
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
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->getChamp()
        );
        
        //Entreprise
        $this->addChamp(
            (new JSChamp())
                ->createAssociation("entreprise", "Entreprise")
                ->setRequired(false)
                ->setDisabled(false)
                ->setColumns(12)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
