<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ClientCrudController extends AbstractCrudController
{
    public const TAB_CLIENT_SECTEUR = [
        'Agroalimentaire' => 0,
        'Banque / Assurance' => 0,
        'Bois / Papier / Carton / Imprimerie' => 0,
        'BTP / Matériaux de construction' => 0,
        'Chimie / Parachimie' => 0,
        'Commerce / Négoce / Distribution' => 0,
        'Édition / Communication / Multimédia' => 0,
        'Électronique / Électricité' => 0,
        'Études et conseils' => 0,
        'Hôtelerie' => 0,
        'Industrie pharmaceutique' => 0,
        'Informatique / Télécoms' => 0,
        'Machines et équipements / Automobile' => 0,
        'Métallurgie / Travail du métal' => 0,
        'Mines' => 0,
        'Plastique / Caoutchouc' => 0,
        'Restauration' => 0,
        'Santé' => 0,
        'Services aux entreprises' => 0,
        'Textile / Habillement / Chaussure' => 0,
        'Transports / Logistique' => 0,
        'Autres Secteurs' => 0
    ];

    public static function getEntityFqcn(): string
    {
        return Client::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Client")
            ->setEntityLabelInPlural("Clients")
            ->setPageTitle("index", "Liste des clients")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            // ...
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('ispersonnemorale')
        ;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom', "Nom"),
            TextField::new('adresse', "Adresse"),
            TelephoneField::new('telephone', "Téléphone"),
            EmailField::new('email', "Email"),
            UrlField::new('siteweb', "Site web")->hideOnIndex(),
            BooleanField::new('ispersonnemorale', "Société"),
            TextField::new('rccm', "RCCM")->hideOnIndex(),
            TextField::new('idnat', "Id. Nationale")->hideOnIndex(),
            TextField::new('numipot', "N°. Impôt")->hideOnIndex(),
            AssociationField::new('entreprise', "Entreprise")->hideOnIndex(),
            NumberField::new('secteur', "Secteur")->hideOnIndex(),
            DateTimeField::new('createdAt', "created At")->hideOnIndex(),
            DateTimeField::new('updatedAt', "Dernière modification")->hideOnForm()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $duplicate = Action::new(self::ACTION_DUPLICATE)
            ->linkToCrudAction('dupliquerClient');//->setCssClass("btn btn-warning");

        $ouvrir = Action::new(self::ACTION_OPEN)
            ->linkToCrudAction('ouvrirClient');

        return $actions
        //Action ouvrir
        ->add(Crud::PAGE_EDIT, $ouvrir)
        ->add(Crud::PAGE_INDEX, $ouvrir)
        //action dupliquer Assureur
        ->add(Crud::PAGE_DETAIL, $duplicate)
        ->add(Crud::PAGE_EDIT, $duplicate)
        ->add(Crud::PAGE_INDEX, $duplicate)
        ->reorder(Crud::PAGE_INDEX, [self::ACTION_OPEN, self::ACTION_DUPLICATE])
        ->reorder(Crud::PAGE_EDIT, [self::ACTION_OPEN, self::ACTION_DUPLICATE]);
    }


    public function dupliquerClient(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();
        $entiteDuplique = clone $entite;
        $this->parent::persistEntity($em, $entiteDuplique);

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($entiteDuplique->getId())
            ->generateUrl();

        return $this->redirect($url);
    }

    public function ouvrirClient(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
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
    
}
