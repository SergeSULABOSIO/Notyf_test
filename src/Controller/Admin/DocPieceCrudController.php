<?php

namespace App\Controller\Admin;

use App\Entity\DocPiece;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Vich\UploaderBundle\Handler\DownloadHandler;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Service\RefactoringJS\Evenements\SuperviseurSujet;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Service\RefactoringJS\JSUIComponents\Document\DocumentUIBuilder;
use App\Service\RefactoringJS\Commandes\ComDefinirObservateursEvenements;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;

class DocPieceCrudController extends AbstractCrudController implements CommandeExecuteur
{
    public const TYPE_INFO_SUR_RISQUE               = "Infos sur le risque";
    public const TYPE_PROPOSITION                   = "Proposition / Cotation / Dévis";
    public const TYPE_CERTIFICAT                    = "Certificat d'assurance";
    public const TYPE_POLICE                        = "Police d'assurance";
    public const TYPE_VIGNETTE                      = "Vignette";
    public const TYPE_REVELE_DE_COMPTE              = "Révelé de Compte (SOA)";
    public const TYPE_CARTE_ROSE                    = "Carte Rose";
    public const TYPE_VOLET_JAUNE                   = "Volet Jaune";
    public const TYPE_BORDEREAU_DE_REASSURANCE      = "Bordereau de Réassurance";
    public const TYPE_FACTURE_DE_REASSURANCE        = "Note de débit de Réassurance";
    public const TYPE_CONDITIONS_PARTICULIERES      = "Conditions Particulières";
    public const TYPE_CONDITIONS_GENERALES          = "Conditions Générales";
    public const TYPE_MANDAT_COURTAGE               = "Mandat de courtage";
    public const TYPE_PREUVE_DE_PAIEMENT            = "Preuve de Paiement (POP)";
    public const TYPE_COPIE_DU_SWIFT                = "Preuve de Paiement (Swift)";
    public const TYPE_FORMULAIRE_DE_PROPOSITION     = "Formulaire de proposition";
    public const TYPE_FACTURE                       = "Facture / Note de débit";
    public const TYPE_NOTE_DE_CREDIT                = "Avoire / Note de crédit";
    public const TYPE_AUTRES                        = "Autre (à préciser)";

    public const TAB_TYPES = [
        self::TYPE_INFO_SUR_RISQUE => 1,
        self::TYPE_PROPOSITION => 2,
        self::TYPE_CERTIFICAT => 3,
        self::TYPE_POLICE => 4,
        self::TYPE_VIGNETTE => 5,
        self::TYPE_REVELE_DE_COMPTE => 6,
        self::TYPE_CARTE_ROSE => 7,
        self::TYPE_VOLET_JAUNE => 8,
        self::TYPE_BORDEREAU_DE_REASSURANCE => 9,
        self::TYPE_FACTURE_DE_REASSURANCE => 10,
        self::TYPE_CONDITIONS_PARTICULIERES => 11,
        self::TYPE_CONDITIONS_GENERALES => 12,
        self::TYPE_MANDAT_COURTAGE => 13,
        self::TYPE_PREUVE_DE_PAIEMENT => 14,
        self::TYPE_COPIE_DU_SWIFT => 15,
        self::TYPE_FORMULAIRE_DE_PROPOSITION => 16,
        self::TYPE_FACTURE => 17,
        self::TYPE_NOTE_DE_CREDIT => 18,
        self::TYPE_AUTRES => 19,
    ];

    public const FORMAT_MS_WORD = "MS Word";
    public const FORMAT_MS_EXCEL = "MS Excel";
    public const FORMAT_MS_PP = "MS Power Point";
    public const FORMAT_PDF = "PDF";
    public const FORMAT_IMG = "Image";
    public const FORMAT_VIDEO = "Vidéo";
    public const FORMAT_AUTRE = "Autre";

    public const TAB_FORMAT = [
        self::FORMAT_AUTRE      => 0,
        self::FORMAT_MS_WORD    => 1,
        self::FORMAT_MS_EXCEL   => 2,
        self::FORMAT_MS_PP      => 3,
        self::FORMAT_PDF        => 4,
        self::FORMAT_IMG        => 5,
        self::FORMAT_VIDEO      => 6,
    ];

    public const ARTICLE_BASE_PATH = 'uploads/documents';
    public const ARTICLE_UPLOAD_DIR = 'public/uploads/documents';
    public ?Crud $crud = null;
    public ?DocumentUIBuilder $uiBuilder = null;


    public function __construct(
        private SuperviseurSujet $superviseurSujet,
        private ServiceDates $serviceDates,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal
    ) {
        $this->uiBuilder = new DocumentUIBuilder();
    }

    public static function getEntityFqcn(): string
    {
        return DocPiece::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new DocPiece(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Pièce")
            ->setEntityLabelInPlural("Pièces")
            ->setPageTitle("index", "Documents")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_BIBLIOTHE])
            // ...
        ;
        return $crud;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $defaultQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            // ->add('categorie')
            // ->add('classeur')
            ->add('cotation')
            ->add('piste')
            ->add('actionCRM')
            ->add('police')
            // ->add('sinistre')
        ;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var DocPiece */
        $documentToDelete = $entityInstance;
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $documentToDelete
        ));
        //déconnexion à la tache
        $documentToDelete->setActionCRM(null);

        //Deconnexion à la cotation
        $documentToDelete->setCotation(null);

        //Déconnexion à la facture
        $documentToDelete->setFacture(null);

        //Déconnexion à la police
        $documentToDelete->setPolice(null);

        //Déconnexion au document
        $documentToDelete->setDocument(null);

        //Déconnexion au paiement
        $documentToDelete->setPaiement(null);

        //destruction définitive du document
        $this->entityManager->remove($documentToDelete);
        $this->entityManager->flush();
        // $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::BIBLIOTHEQUE_PIECE);
    }


    public function createEntity(string $entityFqcn)
    {
        $objet = new DocPiece();
        // $objet = $this->serviceCrossCanal->crossCanal_Piece_setCotation($objet, $this->adminUrlGenerator);
        // $objet = $this->serviceCrossCanal->crossCanal_Piece_setPolice($objet, $this->adminUrlGenerator);
        // $objet = $this->serviceCrossCanal->crossCanal_Piece_setSinistre($objet, $this->adminUrlGenerator);
        // $objet = $this->serviceCrossCanal->crossCanal_Piece_setPOPCom($objet, $this->adminUrlGenerator);
        // $objet = $this->serviceCrossCanal->crossCanal_Piece_setPOPPartenaire($objet, $this->adminUrlGenerator);
        // $objet = $this->serviceCrossCanal->crossCanal_Piece_setPOPTaxe($objet, $this->adminUrlGenerator);
        //$objet->setStartedAt(new DateTimeImmutable("+1 day"));
        //$objet->setEndedAt(new DateTimeImmutable("+7 day"));
        //$objet->setClos(0);

        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $objet
        ));

        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        $instance = $this->getContext()->getEntity()->getInstance();
        if ($this->crud) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        }

        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $instance
        ));

        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $instance,
            $this->crud,
            $this->adminUrlGenerator
        );
        // return $this->servicePreferences->getChamps(new DocPiece(), $this->crud, $this->adminUrlGenerator);
    }

    public function configureActions(Actions $actions): Actions
    {
        // $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)->setIcon('fa-solid fa-copy')
        // ->linkToCrudAction('dupliquerEntite'); //<i class="fa-solid fa-copy"></i>
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>

        $telechargerDoc = Action::new("Télécharger")
            ->setIcon('fa-solid fa-download')->linkToCrudAction('telechargerDocument'); //<i class="fa-solid fa-download"></i>

        // $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
        // ->linkToCrudAction('exporterMSExcels')
        // ->addCssClass('btn btn-primary')
        // ->setIcon('fa-solid fa-file-excel');

        return $actions
            //Sur la page Index - Selection
            //->addBatchAction($exporter_ms_excels)
            //les Updates sur la page détail
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER);
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER); //<i class="fa-solid fa-pen-to-square"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, function (Action $action) {
                return $action->setIcon('fa-regular fa-rectangle-list')->setLabel(DashboardController::ACTION_LISTE); //<i class="fa-regular fa-rectangle-list"></i>
            })
            //Updates sur la page Index
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fas fa-file-word')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER); //<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::BATCH_DELETE, function (Action $action) {
                return $action->setIcon('fa-solid fa-trash')->setLabel(DashboardController::ACTION_SUPPRIMER); //<i class="fa-solid fa-trash"></i>
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            //Updates Sur la page NEW
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->remove(Crud::PAGE_INDEX, Action::NEW)


            //Action ouvrir
            ->add(Crud::PAGE_EDIT, $ouvrir)
            ->add(Crud::PAGE_INDEX, $ouvrir)

            ->add(Crud::PAGE_INDEX, $telechargerDoc)
            ->add(Crud::PAGE_DETAIL, $telechargerDoc)
            ->add(Crud::PAGE_EDIT, $telechargerDoc)
            //action dupliquer Assureur
            // ->add(Crud::PAGE_DETAIL, $duplicate)
            // ->add(Crud::PAGE_EDIT, $duplicate)
            // ->add(Crud::PAGE_INDEX, $duplicate)
            //Reorganisation des boutons
            // ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            // ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            //->setPermission(self::ACTION_ACHEVER_MISSION, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            //->setPermission(self::ACTION_AJOUTER_UN_FEEDBACK, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
        ;
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entite->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function telechargerDocument(DownloadHandler $downloadHandler, AdminContext $context)
    {
        /** @var DocPiece */
        $piece = $context->getEntity()->getInstance();
        return $downloadHandler->downloadObject($piece, $fileField = 'document', $objectClass = null, $fileName = null, $forceDownload = true);
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
