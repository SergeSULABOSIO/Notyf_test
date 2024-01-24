<?php

namespace App\Service\RefactoringJS\JSUIComponents\Paiements;

use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\PaiementCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Facture;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\Parametres\JSPanelRenderer;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class PaiementFormRenderer extends JSPanelRenderer
{
    private ?AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        string $pageName,
        $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function design()
    {
        $this->addOnglet(
            "Informations générales",
            "fa-solid fa-cash-register",
            "Veuillez saisir les informations relatives au paiement."
        );
        $this->addSection(
            "Section principale",
            "fas fa-location-crosshairs",
            10
        );
        $this->addChampAssociation(
            null,
            "facture",
            "Facture",
            false,
            true,
            10,
            null
        );
        $this->addChampChoix(
            null,
            "type",
            "Tye de facture",
            null,
            true,
            6,
            PaiementCrudController::TAB_TYPE_PAIEMENT,
            null
        );
        $this->addChampArgent(
            null,
            "montant",
            "Montant",
            null,
            null,
            2,
            $this->serviceMonnaie->getCodeSaisie()
        );
        $this->addChampDate(
            null,
            "paidAt",
            "Date",
            true,
            null,
            2
        );
        $this->addChampEditeurTexte(
            null,
            "description",
            "Description",
            false,
            null,
            10
        );
        $this->addSection(
            "Références bancaires",
            "fa-solid fa-piggy-bank",
            10
        );
        $this->addChampAssociation(
            null,
            "compteBancaire",
            "Comptes bancaires",
            false,
            null,
            10,
            null
        );
        $this->addSection(
            "Pièces jointes",
            "fa-solid fa-paperclip",
            10
        );
        $this->addChampCollection(
            null,
            "documents",
            "Documents",
            false,
            null,
            10,
            null,
            DocPieceCrudController::class,
            true,
            true
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        /** @var Facture */
        $oFacture = $this->entityManager
            ->getRepository(Facture::class)
            ->find(
                $adminUrlGenerator->get("donnees")["facture"]
            );

        $newChamps = [];
        /** @var FormField */
        foreach ($champs as $champ) {
            $propertName = null;
            if ($champ instanceof FormField) {
                $propertName = $champ->getAsDto()->getLabel();
            } else {
                $propertName = $champ->getAsDto()->getProperty();
            }
            // dd($champ->getAsDto()->getProperty(), $champs);
            if ($oFacture->getType() == FactureCrudController::TAB_TYPE_FACTURE[FactureCrudController::TYPE_FACTURE_PRIME]) {
                switch ($propertName) {
                    case "compteBancaire":
                        # on ne fait absolument rien ici
                        break;
                    case "Références bancaires":
                        # on ne fait absolument rien ici
                        break;

                    default:
                        $newChamps[] = $champ;
                        break;
                }
            }
        }
        //dd($champs);
        // dd($pageName, $type, $objetInstance, "Ancien tableau:", $champs, "Nouveau tableau:", $newChamps);
        return $newChamps;
    }
}
