<?php

namespace App\Controller\Admin;

use App\Service\ServiceTaxes;
use App\Entity\ElementFacture;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceAvenant;
use App\Service\ServiceCrossCanal;
use App\Service\ServiceEntreprise;
use App\Service\ServiceCalculateur;
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

    public function __construct(
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
        //AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em
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
        $this->crud = $crud
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


    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
}
