<?php

namespace App\Controller\Admin;

use App\Entity\Piste;
use DateTimeImmutable;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\Commandes\Commande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use App\Service\RefactoringJS\Commandes\CommandeExecuteur;
use App\Service\RefactoringJS\Evenements\SuperviseurSujet;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use App\Service\RefactoringJS\JSUIComponents\Piste\PisteUIBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use App\Service\RefactoringJS\Commandes\ComDefinirObservateursEvenements;

class PisteCrudController extends AbstractCrudController implements CommandeExecuteur
{
    public const ETAPE_CREATION             = "Creation";
    public const ETAPE_COLLECTE_DE_DONNEES  = "Collecte des données";
    public const ETAPE_PRODUCTION_DES_DEVIS = "Production des cotations";
    public const ETAPE_CONCLUSION           = "Conclusion et émission de l'avenant";

    public const TAB_ETAPES = [
        self::ETAPE_CREATION => 1,
        self::ETAPE_COLLECTE_DE_DONNEES => 2,
        self::ETAPE_PRODUCTION_DES_DEVIS => 3,
        self::ETAPE_CONCLUSION => 4,
    ];

    public ?PisteUIBuilder $uiBuilder = null;
    public ?Crud $crud = null;

    public function __construct(
        private SuperviseurSujet $superviseurSujet,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceAvenant $serviceAvenant,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private ServiceDates $serviceDates,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->uiBuilder = new PisteUIBuilder($this->serviceEntreprise);
    }

    public static function getEntityFqcn(): string
    {
        return Piste::class;
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

        return $filters;
    }

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Piste(), $crud);

        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy HH:mm') // HH:mm:ss
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Piste")
            ->setEntityLabelInPlural("Pistes")
            ->setPageTitle("index", "Liste des pistes")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL])
            // ...
        ;
        return $crud;
    }


    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Piste */
        $pisteToDelete = $entityInstance;
        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $pisteToDelete
        ));
        //destruction des documents
        foreach ($pisteToDelete->getDocuments() as $docu) {
            $pisteToDelete->removeDocument($docu);
        }
        //destruction des polices
        foreach ($pisteToDelete->getPolices() as $police) {
            $pisteToDelete->removePolice($police);
        }
        //destruction des cotations
        foreach ($pisteToDelete->getCotations() as $cotation) {
            $pisteToDelete->removeCotation($cotation);
        }
        //destruction des contacts
        foreach ($pisteToDelete->getContacts() as $contact) {
            $pisteToDelete->removeContact($contact);
        }
        //destruction des actions
        foreach ($pisteToDelete->getActionsCRMs() as $action) {
            foreach ($action->getFeedbacks() as $feedback) {
                $action->removeFeedback($feedback);
            }
            $pisteToDelete->removeActionsCRM($action);
        }
        //destruction définitive de la piste
        $this->entityManager->remove($pisteToDelete);
        $this->entityManager->flush();
    }


    public function createEntity(string $entityFqcn)
    {
        // dd("On est ici!");
        $objet = new Piste();
        $objet->setNom("PISTE N" . (new DateTimeImmutable())->getTimestamp());
        $objet->setEtape(PisteCrudController::TAB_ETAPES[PisteCrudController::ETAPE_CREATION]);
        $objet->setTypeavenant(PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_SOUSCRIPTION]);
        $objet->setExpiredAt(new DateTimeImmutable("+30 day"));
        $objet = $this->serviceCrossCanal->crossCanal_Etape_setEtape($objet, $this->adminUrlGenerator);
        $objet = $this->serviceCrossCanal->crossCanal_Piste_setPolice($objet, $this->adminUrlGenerator);
        $objet->setObjectif("Pour plus d'infos, voire les tâches à exécuter.");

        //Exécuter - Ecouteurs d'évènements
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $objet
        ));
        // $idAvenant = $this->serviceAvenant->generateIdAvenantByReference("0145787878787-2024");
        // dd("IdAvenant", $idAvenant);
        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var Piste */
        $piste = $this->getContext()->getEntity()->getInstance();
        if ($this->crud) {
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $piste);
        }

        //Ecouteurs
        $this->executer(new ComDefinirObservateursEvenements(
            $this->superviseurSujet,
            $this->entityManager,
            $this->serviceEntreprise,
            $this->serviceDates,
            $piste
        ));

        // dd("Ici", $pageName, $piste);
        //dd($piste->getClient()->isExoneree());
        // $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $piste);
        //dd($this->adminUrlGenerator);
        // $this->servicePreferences->setEntite($pageName, $piste);
        // return $this->servicePreferences->getChamps(new Piste(), $this->crud, $this->adminUrlGenerator);

        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $pageName,
            $piste,
            $this->crud,
            $this->adminUrlGenerator
        );
    }


    public function configureActions(Actions $actions): Actions
    {
        //Cross Canal
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel'); //<i class="fa-solid fa-file-excel"></i>


        return $actions
            //Sur la page Index - Selection
            ->addBatchAction($exporter_ms_excels)
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

            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            //Updates Sur la page NEW
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER_ET_CONTINUER);
            })

            //Action ouvrir
            ->add(Crud::PAGE_EDIT, $ouvrir)
            ->add(Crud::PAGE_INDEX, $ouvrir)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)

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

    public function cross_canal_ajouterMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Piste_ajouterMission($context, $adminUrlGenerator));
    }

    public function cross_canal_listerMission(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Piste_listerMission($context, $adminUrlGenerator));
    }

    public function cross_canal_ajouterContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Piste_ajouterContact($context, $adminUrlGenerator));
    }

    public function cross_canal_listerContact(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Piste_listerContact($context, $adminUrlGenerator));
    }

    public function cross_canal_listerCotation(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_Piste_listerCotation($context, $adminUrlGenerator));
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

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        /** @var Piste */
        $piste = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(PisteCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($piste->getId())
            ->generateUrl();
        $this->addFlash("success", "Salut " . $this->serviceEntreprise->getUtilisateur()->getNom() . ". La piste " . $piste->getNom() .  " vient d'être enregistrée avec succès. Vous pouvez maintenant y ajouter d'autres informations.");
        return $this->redirect($url);
    }

    public function executer(?Commande $commande)
    {
        if ($commande != null) {
            $commande->executer();
        }
    }
}
