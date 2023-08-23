<?php

namespace App\Controller\Admin;

use App\Entity\Facture;
use App\Service\ServiceTaxes;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServiceDates;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FactureCrudController extends AbstractCrudController
{
    public const TYPE_FACTURE_COMMISSIONS               = "NOTE DE DEBIT";
    public const TYPE_FACTURE_RETROCOMMISSIONS          = "FATURE POUR RETRO-COMMISSION";
    public const TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA    = "NOTE DE PERCETION - TVA";
    public const TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA   = "NOTE DE PERCETION - REGULATEUR";

    public const TAB_TYPE_FACTURE = [
        self::TYPE_FACTURE_COMMISSIONS              => 0,
        self::TYPE_FACTURE_RETROCOMMISSIONS         => 1,
        self::TYPE_FACTURE_NOTE_DE_PERCEPTION_TVA   => 2,
        self::TYPE_FACTURE_NOTE_DE_PERCEPTION_ARCA  => 3
    ];

    public ?Crud $crud = null;

    public function __construct(
        private ServiceDates $serviceDates,
        private ServiceAvenant $serviceAvenant,
        private ServiceSuppression $serviceSuppression,
        private ServiceCalculateur $serviceCalculateur,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes
    ) {
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
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            //->setDateTimeFormat('dd/MM/yyyy')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("FACTURE")
            ->setEntityLabelInPlural("Facture")
            ->setPageTitle("index", "FACTURES")
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
            ->add(ChoiceFilter::new('type', 'Type de facture')->setChoices(self::TAB_TYPE_FACTURE))
            ->add('partenaire')
            ->add('assureur')
            ->add('piece')
            ->add('paiementCommissions')
            ->add('paiementPartenaires')
            ->add('paiementTaxes')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_MONNAIE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new Facture();
        //$objet = $this->serviceAvenant->setAvenant($objet, $this->adminUrlGenerator);
        //$objet = $this->serviceCrossCanal->crossCanal_Police_setCotation($objet, $this->adminUrlGenerator);
        $objet->setType(self::TAB_TYPE_FACTURE[self::TYPE_FACTURE_COMMISSIONS]);
        $objet->setCreatedAt($this->serviceDates->aujourdhui());
        $objet->setUpdatedAt($this->serviceDates->aujourdhui());
        $objet->setDescription("Facture"); //Date("dmYHis")
        $ref = "ND" . Date("dmYHis") . "/" . $this->serviceEntreprise->getEntreprise()->getNom() . "/" . Date("Y");
        $objet->setReference(strtoupper(str_replace(" ", "", $ref)));
        return $objet;
    }

    public function configureFields(string $pageName): iterable
    {
        $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator);
        //Actualisation des attributs calculables - Merci Seigneur Jésus !
        $this->serviceCalculateur->calculate($this->container, ServiceCalculateur::RUBRIQUE_FACTURE);
        return $this->servicePreferences->getChamps(new Facture(), $this->crud, $this->adminUrlGenerator);
    }
}
