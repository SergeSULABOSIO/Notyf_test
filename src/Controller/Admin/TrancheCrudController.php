<?php

namespace App\Controller\Admin;

use mapped;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Produit;
use App\Entity\Tranche;
use App\Entity\Assureur;
use App\Entity\Partenaire;
use App\Entity\Utilisateur;
use App\Service\RefactoringJS\AutresClasses\ConditionArca;
use App\Service\RefactoringJS\AutresClasses\ConditionAssureur;
use App\Service\RefactoringJS\AutresClasses\ConditionClient;
use App\Service\RefactoringJS\AutresClasses\ConditionDgi;
use App\Service\RefactoringJS\AutresClasses\ConditionPartenaire;
use App\Service\RefactoringJS\AutresClasses\JSAbstractNoteConditionListener;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use App\Service\ServiceCompteBancaire;
use App\Service\ServiceFiltresNonMappes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\Initialisateurs\Facture\ObjetMultiCom;
use App\Service\RefactoringJS\JSUIComponents\Tranche\TrancheUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TrancheCrudController extends AbstractCrudController
{
    public ?Crud $crud = null;
    public $tranche = null;
    public ?TrancheUIBuilder $trancheUIBuilder = null;

    public function __construct(
        private ServiceAvenant $serviceAvenant,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceCompteBancaire $serviceCompteBancaire,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes,
        private ServiceFiltresNonMappes $serviceFiltresNonMappes

    ) {
        $this->trancheUIBuilder = new TrancheUIBuilder();
    }

    public static function getEntityFqcn(): string
    {
        return Tranche::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);

        //On applique le critère basé sur les attributs non mappés dans l'entité
        $defaultQueryBuilder = $this->serviceFiltresNonMappes->appliquerCriteresAttributsNonMappes(
            $searchDto,
            $entityDto,
            $fields,
            $filters,
            function (SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder {
                return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
            }
        );

        //Filtre standard pour Utilisateur et Entreprise
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }

        //FILTRES BASES SUR LES ATTRIBUTS NON MAPPES
        $criteresNonMappes = [
            "validated" => [
                "label" => "Validée?",
                "class" => null,
                "defaultValue" => true,
                "userValues" => null,
                "options" => [
                    "Oui" => true,
                    "Non" => false,
                ],
                "multipleChoices" => false,
                "joiningEntity" => "cotation",
            ],
            "piste" => [
                "label" => "Pistes",
                "class" => Piste::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "police" => [
                "label" => "Polices",
                "class" => Police::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "client" => [
                "label" => "Clients",
                "class" => Client::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "partenaire" => [
                "label" => "Partenaires",
                "class" => Partenaire::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "produit" => [
                "label" => "Produits",
                "class" => Produit::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "assureur" => [
                "label" => "Assureurs",
                "class" => Assureur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "gestionnaire" => [
                "label" => "Gestionnaire",
                "class" => Utilisateur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
            "assistant" => [
                "label" => "Assistant",
                "class" => Utilisateur::class,
                "defaultValue" => [],
                "userValues" => [],
                "options" => [],
                "multipleChoices" => true,
                "joiningEntity" => "cotation",
            ],
        ];
        $filters = $this->serviceFiltresNonMappes->definirFiltreNonMappe($criteresNonMappes, $filters);
        //dd($filters);
        return $filters
            ->add(DateTimeFilter::new('startedAt', "Début de la tranche"))
            ->add(DateTimeFilter::new('endedAt', "Echéance de la tranche"))
            ->add(DateTimeFilter::new('dateEffet', "Début de la police"))
            ->add(DateTimeFilter::new('dateExpiration', "Echéance de la police"))
            //->add('police')
            //->add('utilisateur')
            //->add('actionCRMs')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Tranche(), $crud);

        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy') // HH:mm:ss
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Tranche")
            ->setEntityLabelInPlural("Tranches")
            ->setPageTitle("index", "Liste des tranches")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
        return $crud;
    }


    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::PRODUCTION_TRANCHE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Tranche();
        $objet->setNom("");
        $objet->setTaux(100);
        $objet->setCreatedAt($this->serviceDates->aujourdhui());
        $objet->setUpdatedAt($this->serviceDates->aujourdhui());
        $objet->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $objet->setEntreprise($this->serviceEntreprise->getEntreprise());
        //dd($objet);
        return $objet;
    }



    public function configureFields(string $pageName): iterable
    {
        $this->tranche = $this->getContext()->getEntity()->getInstance();
        if ($this->crud != null) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->tranche);
        }

        return $this->trancheUIBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $this->tranche,
            $this->crud,
            $this->adminUrlGenerator
        );
    }

    public function configureActions(Actions $actions): Actions
    {
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>

        $creerNotePourClient = Action::new("Facture pour Client")
            ->setIcon('fas fa-person-shelter')
            ->displayIf(static function (Tranche $tranche) {
                return $tranche->canInvoiceClient();
            })
            ->linkToCrudAction('creerNotePourClient');

        $creerNotePourPartenaire = Action::new("Note crédit pour Partenaire")
            ->setIcon('fas fa-handshake')
            ->displayIf(static function (Tranche $tranche) {
                return $tranche->canInvoicePartenaire();
            })
            ->linkToCrudAction('creerNotePourPartenaire');


        $creerNotePourAssureur = Action::new("Facture pour Assureur")
            ->setIcon('fas fa-umbrella')
            ->displayIf(static function (Tranche $tranche) {
                return $tranche->canInvoiceAssureur();
            })
            ->linkToCrudAction('creerNotePourAssureur');

        $creerNotePourDGI = Action::new("Note pour Autorité fiscale")
            ->setIcon('fas fa-landmark-dome')
            ->displayIf(static function (Tranche $tranche) {
                return $tranche->canInvoiceDGI();
            })
            ->linkToCrudAction('creerNotePourDGI');

        $creerNotePourARCA = Action::new("Note pour Régulateur")
            ->setIcon('fas fa-landmark-dome')
            ->displayIf(static function (Tranche $tranche) {
                return $tranche->canInvoiceARCA();
            })
            ->linkToCrudAction('creerNotePourARCA');

        $batch_exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel'); //<i class="fa-solid fa-file-excel"></i>

        $batch_creerNotePourClient = Action::new("produire_note_client", "Facture pour Client")
            ->linkToCrudAction('batchCreerNotePourClient')
            ->addCssClass('btn btn-primary')
            ->setIcon('fas fa-person-shelter');

        $batch_creerNotePourAssureur = Action::new("produire_note_assureur", "Facture pour Assureur")
            ->linkToCrudAction('batchCreerNotePourAssureur')
            ->addCssClass('btn btn-primary')
            ->setIcon('fas fa-umbrella');

        $batch_creerNotePourDGI = Action::new("produire_note_dgi", "Note pour Autorité fiscale")
            ->linkToCrudAction('batchCreerNotePourDGI')
            ->addCssClass('btn btn-primary')
            ->setIcon('fas fa-landmark-dome');

        $batch_creerNotePourARCA = Action::new("produire_note_arca", "Note pour Régulateur")
            ->linkToCrudAction('batchCreerNotePourARCA')
            ->addCssClass('btn btn-primary')
            ->setIcon('fas fa-landmark-dome');

        $batch_creerNotePourPartenaire = Action::new("produire_note_partenaire", "Note de crédit pour Partenaire")
            ->linkToCrudAction('batchCreerNotePourPartenaire')
            ->addCssClass('btn btn-primary')
            ->setIcon('fas fa-handshake');

        return $actions
            //Sur la page Index - Selection
            ->addBatchAction($batch_exporter_ms_excels)
            ->addBatchAction($batch_creerNotePourARCA)
            ->addBatchAction($batch_creerNotePourDGI)
            ->addBatchAction($batch_creerNotePourPartenaire)
            ->addBatchAction($batch_creerNotePourClient)
            ->addBatchAction($batch_creerNotePourAssureur)
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
                return $action->setIcon('fas fa-location-crosshairs')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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

            // ->add(Crud::PAGE_INDEX, $factureTaxeAssureur)
            // ->add(Crud::PAGE_INDEX, $factureTaxeCourtier)
            // ->add(Crud::PAGE_INDEX, $factureRetroCom)
            // ->add(Crud::PAGE_INDEX, $factureCommissionLocale)
            // ->add(Crud::PAGE_INDEX, $factureCommissionReassurance)
            // ->add(Crud::PAGE_INDEX, $factureCommissionFronting)
            // ->add(Crud::PAGE_INDEX, $factureFraisGestion)
            // ->add(Crud::PAGE_INDEX, $facturePrime)

            ->add(Crud::PAGE_INDEX, $creerNotePourARCA)
            ->add(Crud::PAGE_INDEX, $creerNotePourDGI)
            ->add(Crud::PAGE_INDEX, $creerNotePourPartenaire)
            ->add(Crud::PAGE_INDEX, $creerNotePourClient)
            ->add(Crud::PAGE_INDEX, $creerNotePourAssureur)

            // ->add(Crud::PAGE_INDEX, $factureMultiCommissions)

            //Action ouvrir
            ->add(Crud::PAGE_EDIT, $ouvrir)
            ->add(Crud::PAGE_INDEX, $ouvrir)

            //Reorganisation des boutons
            // ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            // ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])

            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION]);
    }

    public function exporterMSExcels(BatchActionDto $batchActionDto)
    {
        $className = $batchActionDto->getEntityFqcn();
        $entityManager = $this->container->get('doctrine')->getManagerForClass($className);

        dd($batchActionDto->getEntityIds());

        foreach ($batchActionDto->getEntityIds() as $id) {
            $user = $entityManager->find($className, $id);
            $user->approve();
        }
        $entityManager->flush();
        return $this->redirect($batchActionDto->getReferrerUrl());
    }


    private function canBatchInvoice(?JSAbstractNoteConditionListener $ecouteur): ?array
    {
        $user = $this->serviceEntreprise->getUtilisateur()->getNom();
        $reponse = [
            "Message"   => $user . ", Ok pour production de la facture.",
            "Cible"     => $ecouteur->getCible(),
            "Ids"       => [],
            "Action"    => true
        ];
        if ($ecouteur->isVide()) {
            $reponse = [
                "Message"   => $user . ", Impossible d'effectuer cette opération car vous n'avez séléctionné aucune tranche.",
                "Cible"     => $ecouteur->getCible(),
                "Ids"       => [],
                "Action"    => false
            ];
        } else {
            /** @var Tranche */
            foreach ($ecouteur->getTabTranches() as $tranche) {
                if (
                    //Les conditions afin que la facturation soit possible
                    $ecouteur->isSameCible($ecouteur->getCible(), $tranche) == false
                ) {
                    $reponse = [
                        "Message"  => $user . ", Toutes ces " . (count($ecouteur->getTabTranches())) . " tranche(s) ne concerne pas seul(e) " . $ecouteur->getCible() . ". Veuillez vous assurer, en se servant de filtres, que toutes ces tranches ne puissent concerner que " . $ecouteur->getCible() . ".",
                        "Cible"    => $ecouteur->getCible(),
                        "Ids"       => [],
                        "Action"   => false
                    ];
                    // dd("Ici");
                    break;
                }
            }
            $reponse["Ids"] = $ecouteur->getEntityIdsAfterCanInvoiceFilter();
            // dd($reponse["Ids"]);
            if (count($reponse["Ids"]) == 0) {
                $reponse["Message"] = $user . ", Désolé car il n'est pas possible d'émettre de note de débit (ou de crédit) soit puisqu'il n'y a rien collecter (ou payer), soit puisque les notes ont déjà été émises.";
                $reponse["Action"] = false;
            }
        }
        // dd($reponse);
        return $reponse;
    }

    private function retrieveTabTranches(BatchActionDto $batchActionDto): ?array
    {
        $className = $batchActionDto->getEntityFqcn();
        $entityManager = $this->container->get('doctrine')->getManagerForClass($className);
        $tabTranches = [];
        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var Tranche */
            $tabTranches[] = $entityManager->find($className, $id);
        }
        return $tabTranches;
    }

    //ACTION POUR DESTINATION PAR LOT DES TRANCHES
    public function batchCreerNotePourClient(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->invoiceClient($this->retrieveTabTranches($batchActionDto), $adminUrlGenerator, $batchActionDto->getReferrerUrl());
    }

    public function batchCreerNotePourAssureur(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->invoiceAssureur($this->retrieveTabTranches($batchActionDto), $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    public function batchCreerNotePourPartenaire(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->invoicePartenaire($this->retrieveTabTranches($batchActionDto), $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    public function batchCreerNotePourDGI(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->invoiceDGI($this->retrieveTabTranches($batchActionDto), $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    public function batchCreerNotePourARCA(BatchActionDto $batchActionDto, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->invoiceARCA($this->retrieveTabTranches($batchActionDto), $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }


    //ACTION POUR DESTINATION TRANCHE INDIVIDUELLE
    public function creerNotePourClient(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->invoiceClient([($context->getEntity()->getInstance())], $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    public function creerNotePourAssureur(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->invoiceAssureur([($context->getEntity()->getInstance())], $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    public function creerNotePourPartenaire(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->invoicePartenaire([($context->getEntity()->getInstance())], $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    public function creerNotePourDGI(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->invoiceDGI([($context->getEntity()->getInstance())], $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    public function creerNotePourARCA(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->invoiceARCA([($context->getEntity()->getInstance())], $adminUrlGenerator, $adminUrlGenerator->generateUrl());
    }

    private function invoiceClient(?array $tabTranches, AdminUrlGenerator $adminUrlGenerator, $url): RedirectResponse
    {
        $reponse = $this->canBatchInvoice(new ConditionClient($tabTranches));
        if ($reponse["Action"] == true) {
            return $this->redirect(
                $this->editFactureDestination(
                    $reponse["Ids"],
                    FactureCrudController::DESTINATION_CLIENT,
                    $adminUrlGenerator
                )
            );
        }
        $this->addFlash("danger", $reponse["Message"]);
        return $this->redirect($url);
    }

    private function invoiceAssureur(?array $tabTranches, AdminUrlGenerator $adminUrlGenerator, $url): RedirectResponse
    {
        $reponse = $this->canBatchInvoice(new ConditionAssureur($tabTranches));
        if ($reponse["Action"] == true) {
            return $this->redirect(
                $this->editFactureDestination(
                    $reponse["Ids"],
                    FactureCrudController::DESTINATION_ASSUREUR,
                    $adminUrlGenerator
                )
            );
        }
        $this->addFlash("danger", $reponse["Message"]);
        return $this->redirect($url);
    }

    private function invoicePartenaire(?array $tabTranches, AdminUrlGenerator $adminUrlGenerator, $url): RedirectResponse
    {
        $reponse = $this->canBatchInvoice(new ConditionPartenaire($tabTranches));
        if ($reponse["Action"] == true) {
            return $this->redirect(
                $this->editFactureDestination(
                    $reponse["Ids"],
                    FactureCrudController::DESTINATION_PARTENAIRE,
                    $adminUrlGenerator
                )
            );
        }
        $this->addFlash("danger", $reponse["Message"]);
        return $this->redirect($url);
    }

    private function invoiceDGI(?array $tabTranches, AdminUrlGenerator $adminUrlGenerator, $url): RedirectResponse
    {
        $reponse = $this->canBatchInvoice(new ConditionDgi($tabTranches));
        if ($reponse["Action"] == true) {
            return $this->redirect(
                $this->editFactureDestination(
                    $reponse["Ids"],
                    FactureCrudController::DESTINATION_DGI,
                    $adminUrlGenerator
                )
            );
        }
        $this->addFlash("danger", $reponse["Message"]);
        return $this->redirect($url);
    }

    private function invoiceARCA(?array $tabTranches, AdminUrlGenerator $adminUrlGenerator, $url): RedirectResponse
    {
        $reponse = $this->canBatchInvoice(new ConditionArca($tabTranches));
        if ($reponse["Action"] == true) {
            return $this->redirect(
                $this->editFactureDestination(
                    $reponse["Ids"],
                    FactureCrudController::DESTINATION_ARCA,
                    $adminUrlGenerator
                )
            );
        }
        $this->addFlash("danger", $reponse["Message"]);
        return $this->redirect($url);
    }


    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Tranche */
        $entite = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entite->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    // Pas besoin ce décorateur pour l'instant.
    #[Route('/multiCom', name: 'multiCom')]
    public function facturerMultiCommissions(Request $request = null, AdminContext $context = null, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em, ServiceTaxes $serviceTaxes): Response
    {
        /** @var Tranche */
        $tranche = null;
        if ($context != null) {
            $tranche = $context->getEntity()->getInstance();
        }

        $objetMultiCom = new ObjetMultiCom(
            $serviceTaxes->getTaxe(true),
            $serviceTaxes->getTaxe(false)
        );
        $objetMultiCom
            ->setProduireNDPrime($tranche->getPremiumInvoiceDetails()['produire'])
            ->setProduireNDFraisGestion($tranche->getFraisGestionInvoiceDetails()['produire'])
            ->setProduireNDComLocale($tranche->getComLocaleInvoiceDetails()['produire'])
            ->setProduireNDComReassurance($tranche->getComReassuranceInvoiceDetails()['produire'])
            ->setProduireNDComFronting($tranche->getComFrontingInvoiceDetails()['produire'])
            ->setProduireNCRetrocommission($tranche->getRetrocomInvoiceDetails()['produire'])
            ->setProduireNCTaxeCourtier($tranche->getTaxCourtierInvoiceDetails()['produire'])
            ->setProduireNCTaxeAssureur($tranche->getTaxAssureurInvoiceDetails()['produire']);

        $taxeCourtier = $serviceTaxes->getTaxe(true);
        $taxeAssureur = $serviceTaxes->getTaxe(false);

        $formulaire = $this->createFormBuilder($objetMultiCom)
            ->add(
                "produireNDPrime",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour prime d'assurance.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDFraisGestion",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour frais de gestion.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDComLocale",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour commission locale / ordinaire.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDComReassurance",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour commission de réassurance.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNDComFronting",
                CheckboxType::class,
                [
                    "label" => "Note de débit pour commission sur fronting / commission de cession facultative.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNCRetrocommission",
                CheckboxType::class,
                [
                    "label" => "Note de crédit pour rétro-commission.",
                    'required' => false,
                ]
            )
            ->add(
                "produireNCTaxeCourtier",
                CheckboxType::class,
                [
                    "label" => "Note de crédit pour les frais " . $taxeCourtier->getNom() . " destinés à \"" . $taxeCourtier->getOrganisation() . "\".",
                    'required' => false,
                ]
            )
            ->add(
                "produireNCTaxeAssureur",
                CheckboxType::class,
                [
                    "label" => "Note de crédit pour les frais " . $taxeAssureur->getNom() . " destinés à \"" . $taxeAssureur->getOrganisation() . "\".",
                    'required' => false,
                ]
            )
            ->getForm();

        $formulaire->handleRequest($request);
        // dd($formulaire);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            /** @var ObjetMultiCom */
            $objetReponse = $formulaire->getData();
            // dd("Réponse de l'utilisateur:", $objetReponse);

            //On se redirige vers la page des facture
            //Mais l'idéal c'est de filtrer les factures de cette tranche uniquement
            $url = $adminUrlGenerator
                ->setController(TrancheCrudController::class)
                ->setAction(Action::INDEX)
                ->setEntityId(null)
                ->generateUrl();
            $this->addFlash("success", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". Les factures/notes ont été générées avec succès.");
            return $this->redirect($url);
        }
        // dd("Ici - MultiCommissions", $objetMultiCom, $formulaire);
        return $this->render(
            'admin/segment/view_multi_com.html.twig',
            [
                'form' => $formulaire,
                'tranche' => $tranche,
            ]
        );
    }

    private function editFactureDestination(?array $tabIdTranches, ?string $destination, AdminUrlGenerator $adminUrlGenerator): ?string
    {
        return $this->serviceCrossCanal->crossCanal_creer_facture(
            $adminUrlGenerator,
            $tabIdTranches,
            $destination
        );
    }
}
