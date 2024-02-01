<?php

namespace App\Controller\Admin;


use App\Entity\Facture;
use App\Entity\Assureur;
use App\Entity\Partenaire;
use App\Service\ServiceDates;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceFacture;
use App\Service\ServiceMonnaie;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FactureCrudController extends AbstractCrudController
{
    public ?Facture $facture = null;
    public const TYPE_FACTURE_PRIME                     = "PRIME D'ASSURANCE";
    public const TYPE_FACTURE_FRAIS_DE_GESTION          = "FRAIS DE GESTION";
    public const TYPE_FACTURE_COMMISSION_LOCALE         = "COMMISSION LOCALE";
    public const TYPE_FACTURE_COMMISSION_REASSURANCE    = "COMMISSION DE REASSURANCE";
    public const TYPE_FACTURE_COMMISSION_FRONTING       = "COMMISSION SUR FRONTING / CESSION";
    public const TYPE_FACTURE_RETROCOMMISSIONS          = "RETRO-COMMISSION";
    public const TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA    = "TVA";
    public const TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA   = "REGULATEUR";

    public const TAB_TYPE_FACTURE = [
        self::TYPE_FACTURE_COMMISSION_LOCALE        => 0,
        self::TYPE_FACTURE_RETROCOMMISSIONS         => 1,
        self::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA   => 2,
        self::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA  => 3,
        self::TYPE_FACTURE_FRAIS_DE_GESTION         => 4,
        self::TYPE_FACTURE_PRIME                    => 5,
        self::TYPE_FACTURE_COMMISSION_LOCALE        => 6,
        self::TYPE_FACTURE_COMMISSION_FRONTING      => 7,
        self::TYPE_FACTURE_COMMISSION_REASSURANCE   => 8
    ];

    public const STATUS_FACTURE_IMPAYEE     = "Impayée";
    public const STATUS_FACTURE_ENCOURS     = "En cours...";
    public const STATUS_FACTURE_SOLDEE      = "Soldée";

    public const TAB_STATUS_FACTURE = [
        self::STATUS_FACTURE_IMPAYEE    => 0,
        self::STATUS_FACTURE_ENCOURS    => 1,
        self::STATUS_FACTURE_SOLDEE     => 2,
    ];

    public ?Crud $crud = null;

    public function __construct(
        private ServiceMonnaie $serviceMonnaie,
        private ServiceFacture $serviceFacture,
        private ServiceDates $serviceDates,
        private ServiceAvenant $serviceAvenant,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes
    ) {
        //$this->dompdf = new Dompdf();
    }

    public static function getEntityFqcn(): string
    {
        return Facture::class;
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

    public function configureCrud(Crud $crud): Crud
    {
        //Application de la préférence sur la taille de la liste
        $this->servicePreferences->appliquerPreferenceTaille(new Facture(), $crud);
        $this->crud = $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss') // 
            ->setDateFormat('dd/MM/yyyy à HH:mm:ss')
            ->setHelp(Crud::PAGE_NEW, "Founissez les informations recquises puis validez le formulaire pour les sauvegarder.")
            ->setHelp(Crud::PAGE_INDEX, "Résultat du filtrage.")
            ->setHelp(Crud::PAGE_DETAIL, "Information détailée sur l'enregistrement séléctioné.")
            ->setHelp(Crud::PAGE_EDIT, "Mise à jour d'un enregistrement. N'oubliez pas de valider le formulaire.")
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Facture / Note de débit")
            ->setEntityLabelInPlural("Factures")
            ->setPageTitle("index", "Factures")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES])
            // ...
        ;
        return $crud;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            ->add(ChoiceFilter::new('type', PreferenceCrudController::PREF_FIN_FACTURE_TYPE)->setChoices(self::TAB_TYPE_FACTURE))
            ->add(ChoiceFilter::new('status', PreferenceCrudController::PREF_FIN_FACTURE_STATUS)->setChoices(self::TAB_STATUS_FACTURE))
            ->add('paiements')
            ->add('partenaire')
            ->add('assureur')
            ->add('piece')
            ->add('elementFactures')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_FACTURE);
    }

    public function createEntity(string $entityFqcn)
    {
        return $this->serviceFacture->initFature($this->adminUrlGenerator);
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName == Crud::PAGE_EDIT) {
            /** @var Facture */
            $this->facture = $this->getContext()->getEntity()->getInstance();
        }
        $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->facture);
        return $this->servicePreferences->getChamps(new Facture(), $this->crud, $this->adminUrlGenerator);
    }

    public function configureActions(Actions $actions): Actions
    {
        $generer_facture = Action::new(DashboardController::ACTION_GENERER_FACTURE_PDF)
            ->setIcon('fa-solid fa-receipt') //<i class="fa-solid fa-download"></i>
            ->linkToCrudAction('genererFacturePDF');

        $payer_facture = Action::new(DashboardController::ACTION_AJOUTER_PAIEMENT)
            ->setIcon('fa-solid fa-cash-register') //<i class="fa-solid fa-download"></i>
            ->displayIf(static function (?Facture $facture) {
                return ($facture->getTotalDu() - $facture->getTotalRecu()) != 0;
            })
            ->linkToCrudAction('payerFacture');

        $generer_bordereau = Action::new(DashboardController::ACTION_GENERER_BORDEREAU_PDF)
            ->setIcon('fa-solid fa-expand') //<i class="fa-solid fa-expand"></i>
            ->linkToCrudAction('genererBordereauPDF');

        $duplicate = Action::new(DashboardController::ACTION_DUPLICATE)
            ->setIcon('fa-solid fa-copy')
            ->linkToCrudAction('dupliquerEntite');
        $modifier = Action::new(DashboardController::ACTION_MODIFIER)
            ->setIcon('fa-solid fa-pen-to-square')
            //->addCssClass('btn btn-primary')
            ->linkToCrudAction('editEntite');
        $ouvrir = Action::new(DashboardController::ACTION_OPEN)
            ->setIcon('fa-solid fa-eye')
            ->linkToCrudAction('ouvrirEntite'); //<i class="fa-solid fa-eye"></i>
        $exporter_ms_excels = Action::new("exporter_ms_excels", DashboardController::ACTION_EXPORTER_EXCELS)
            ->linkToCrudAction('exporterMSExcels')
            ->addCssClass('btn btn-primary')
            ->setIcon('fa-solid fa-file-excel');

        $actions
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
                return $action->setIcon('fas fa-file-shield')->setCssClass('btn btn-primary')->setLabel(DashboardController::ACTION_AJOUTER);
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

            //Action ouvrir
            ->add(Crud::PAGE_EDIT, $ouvrir)
            ->add(Crud::PAGE_INDEX, $ouvrir)

            //action custom Modifier
            ->add(Crud::PAGE_INDEX, $modifier)
            ->add(Crud::PAGE_DETAIL, $modifier)
            ->update(Crud::PAGE_DETAIL, DashboardController::ACTION_MODIFIER, function (Action $action) {
                return $action->addCssClass('btn btn-primary'); //<i class="fa-solid fa-floppy-disk"></i>
            })


            //action dupliquer Assureur
            ->add(Crud::PAGE_DETAIL, $duplicate)
            ->add(Crud::PAGE_EDIT, $duplicate)
            ->add(Crud::PAGE_INDEX, $duplicate)

            ->add(Crud::PAGE_DETAIL, $generer_bordereau)
            ->add(Crud::PAGE_INDEX, $generer_bordereau)
            ->update(Crud::PAGE_DETAIL, DashboardController::ACTION_GENERER_BORDEREAU_PDF, function (Action $action) {
                return $action->addCssClass('btn btn-warning'); //<i class="fa-solid fa-floppy-disk"></i>
            })

            ->add(Crud::PAGE_DETAIL, $generer_facture)
            ->add(Crud::PAGE_INDEX, $generer_facture)
            ->update(Crud::PAGE_DETAIL, DashboardController::ACTION_GENERER_FACTURE_PDF, function (Action $action) {
                return $action->addCssClass('btn btn-primary'); //<i class="fa-solid fa-floppy-disk"></i>
            })

            ->add(Crud::PAGE_DETAIL, $payer_facture)
            ->add(Crud::PAGE_INDEX, $payer_facture)

            //Application des roles
            ->setPermission(Action::NEW, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::EDIT, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::BATCH_DELETE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_ADD_ANOTHER, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_CONTINUE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(Action::SAVE_AND_RETURN, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])
            ->setPermission(DashboardController::ACTION_DUPLICATE, UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION])

            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)

            //Reorganisation des boutons
            ->reorder(Crud::PAGE_INDEX, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            ->reorder(Crud::PAGE_EDIT, [DashboardController::ACTION_OPEN, DashboardController::ACTION_DUPLICATE])
            ->reorder(Crud::PAGE_DETAIL, [
                DashboardController::ACTION_DUPLICATE,
                Action::INDEX,
                DashboardController::ACTION_MODIFIER,
                Action::DELETE,
            ]);

        return $actions;
    }

    public function dupliquerEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();
        $entiteDuplique = clone $entite;
        parent::persistEntity($em, $entiteDuplique);
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entiteDuplique->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function editEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Facture */
        $facture = $context->getEntity()->getInstance();
        return $this->redirect($this->serviceCrossCanal->crossCanal_modifier_facture($adminUrlGenerator, $facture));
    }

    public function ouvrirEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Facture */
        $facture = $context->getEntity()->getInstance();
        return $this->redirect($this->serviceCrossCanal->crossCanal_ouvrir_facture($adminUrlGenerator, $facture));
    }

    public function genererFacturePDF(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Facture */
        $facture = $context->getEntity()->getInstance();
        $contenuHtml = $this->renderView('pdf/instances/note/index.html.twig', $this->getDataTransform($facture, false));
        return $this->serviceFacture->visualiserFacture($facture, $contenuHtml);
    }

    public function genererBordereauPDF(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /** @var Facture */
        $facture = $context->getEntity()->getInstance();
        $contenuHtml = $this->renderView('pdf/instances/bordereau/index.html.twig', $this->getDataTransform($facture, true));
        return $this->serviceFacture->visualiserBordereau($facture, $contenuHtml);
    }

    public function getDataTransform(Facture $facture, bool $isBordereau): array{
        $lienImage = $this->getParameter('kernel.project_dir') . '/public/icones/icon04.png';
        //dd(count($facture->getElementFactures()));

        //On actualise les attributs calculables des Polices
        // foreach ($facture->getElementFactures() as $ef) {
        //     /** @var Tranche  */
        //     $pol = $ef->getTranche();
        //     $this->serviceCalculateur->updatePoliceCalculableFileds($pol);
        // }

        $data = [
            'imageSrc'      => $this->serviceFacture->imageToBase64($lienImage),
            'facture'       => $facture,
            'nature'        => $this->serviceFacture->getType($facture->getType()),
            'pour'          => $this->getPour($facture),
            'monnaie'       => $this->serviceMonnaie->getMonnaie_Affichage(),
            'taxe_courtier' => $this->serviceTaxes->getTaxe(true),
            'taxe_assureur' => $this->serviceTaxes->getTaxe(false),
            'isBordereau'   => $isBordereau == true ? 1 : 0,
        ];
        //dd($data);
        return $data;
    }

    private function getPour(?Facture $facture)
    {
        switch ($this->serviceFacture->getType($facture->getType())) {
            case FactureCrudController::TYPE_FACTURE_COMMISSION_LOCALE || FactureCrudController::TYPE_FACTURE_COMMISSION_FRONTING || FactureCrudController::TYPE_FACTURE_COMMISSION_REASSURANCE:
                /** @var Assureur */
                $assureur = $facture->getAssureur();
                return "<span class = 'texte-gras'>" . $assureur->getNom() . ",</span></br>" . $assureur->getTelephone() . ", " . $assureur->getAdresse();
                break;
            case FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION:
                return "<span class = 'texte-gras'>" . $facture->getAutreTiers() . "</span>";
                break;
            case FactureCrudController::TYPE_FACTURE_FRAIS_DE_GESTION:
                return "<span class = 'texte-gras'>" . $facture->getAutreTiers() . "</span>";
                break;
            case FactureCrudController::TYPE_FACTURE_RETROCOMMISSIONS:
                /** @var Partenaire */
                $partenaire = $facture->getPartenaire();
                return "<span class = 'texte-gras'>" . $partenaire->getNom() . ",</span></br>" . $partenaire->getEmail() . ", " . $partenaire->getAdresse();
                break;
            case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA:
                return "<span class = 'texte-gras'>" . $facture->getAutreTiers() . "</span>";
                break;
            case FactureCrudController::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA:
                return "<span class = 'texte-gras'>" . $facture->getAutreTiers() . "</span>";
                break;
            default:
                return "Inconnu";
                break;
        }
    }
    
    public function exporterMSExcels(BatchActionDto $batchActionDto){
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

    public function payerFacture(AdminContext $context, AdminUrlGenerator $adminUrlGenerator)
    {
        return $this->redirect($this->serviceCrossCanal->crossCanal_payerFacture($context, $adminUrlGenerator));
    }
}
