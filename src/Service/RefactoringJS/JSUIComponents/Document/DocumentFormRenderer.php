<?php

namespace App\Service\RefactoringJS\JSUIComponents\Document;

use App\Entity\DocPiece;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use Vich\UploaderBundle\Form\Type\VichFileType;
use App\Controller\Admin\DocPieceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class DocumentFormRenderer extends JSPanelRenderer
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
        if ($this->objetInstance instanceof DocPiece) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-file-word') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Une pièce est un document de quel que format que ce soit.")
                ->setColumns($column)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_BIB_DOCUMENT_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //Nom Type
        $this->addChamp(
            (new JSChamp())
                ->createChoix('type', PreferenceCrudController::PREF_BIB_DOCUMENT_TYPE)
                ->setChoices(DocPieceCrudController::TAB_TYPES)
                ->setColumns($column)
                ->getChamp()
        );
        //Document
        $this->addChamp(
            (new JSChamp())
                ->createTexte('document', "Pièce jointe")
                ->setFormType(VichFileType::class)
                ->setFormTypeOptions("download_label", "Ouvrir le fichier")
                ->setFormTypeOptions("delete_label", "Supprimer")
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
