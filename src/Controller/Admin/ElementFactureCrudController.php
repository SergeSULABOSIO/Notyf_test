<?php

namespace App\Controller\Admin;

use App\Service\ServiceTaxes;
use App\Entity\ElementFacture;
use App\Service\RefactoringJS\JSUIComponents\ElementFacture\ElementFactureUIBuilder;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
use App\Service\ServiceDates;
use App\Service\ServiceMonnaie;
use App\Service\ServicePreferences;
use App\Service\ServiceSuppression;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ElementFactureCrudController extends AbstractCrudController
{

    public ?Crud $crud = null;
    public $instance = null;
    public ?ElementFactureUIBuilder $uiBuilder = null;

    public function __construct(
        private ServiceDates $serviceDates,
        private ServiceAvenant $serviceAvenant,
        private ServiceSuppression $serviceSuppression,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences,
        private ServiceCrossCanal $serviceCrossCanal,
        private AdminUrlGenerator $adminUrlGenerator,
        private ServiceTaxes $serviceTaxes,
        private ServiceMonnaie $serviceMonnaie
    ) {
        //AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em
        $this->uiBuilder = new ElementFactureUIBuilder();
    }

    public static function getEntityFqcn(): string
    {
        return ElementFacture::class;
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
        $this->servicePreferences->appliquerPreferenceTaille(new ElementFacture(), $crud);
        $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            //->setDateTimeFormat('dd/MM/yyyy')
            ->setDateFormat('dd/MM/yyyy')
            //->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("ELEMENTE")
            ->setEntityLabelInPlural("Elément")
            ->setPageTitle("index", "ELEMENTS")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES])
            // ...
        ;
        $this->crud = $crud;
        return $crud;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters
            ->add('facture');
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->serviceSuppression->supprimer($entityInstance, ServiceSuppression::FINANCE_ELEMENT_FACTURE);
    }

    public function createEntity(string $entityFqcn)
    {
        $objet = new ElementFacture();
        $objet->setEntreprise($this->serviceEntreprise->getEntreprise());
        $objet->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $objet->setCreatedAt($this->serviceDates->aujourdhui());
        $objet->setUpdatedAt($this->serviceDates->aujourdhui());
        // dd("Ici");
        //$objet->setMontant(0);
        //$objet->setFacture(null);
        //$objet = $this->serviceAvenant->setAvenant($objet, $this->adminUrlGenerator);
        //$objet = $this->serviceCrossCanal->crossCanal_Police_setCotation($objet, $this->adminUrlGenerator);
        return $objet;
    }


    public function configureFields(string $pageName): iterable
    {
        $this->instance = $this->getContext()->getEntity()->getInstance();
        if($this->crud != null){
            $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->instance);
        }
        return $this->uiBuilder->render(
            $this->entityManager,
            $this->serviceMonnaie,
            $this->serviceTaxes,
            $this->instance,
            $this->instance,
            $this->crud,
            $this->adminUrlGenerator
        );


        // if($this->crud){
        //     $this->crud = $this->serviceCrossCanal->crossCanal_setTitrePage($this->crud, $this->adminUrlGenerator, $this->getContext()->getEntity()->getInstance());
        // }
        // //Actualisation des attributs calculables - Merci Seigneur Jésus !
        // return $this->servicePreferences->getChamps(new ElementFacture(), $this->crud, $this->adminUrlGenerator);
    }
}
