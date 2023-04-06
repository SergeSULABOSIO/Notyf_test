<?php

namespace App\Controller\Admin;

use App\Entity\Assureur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AssureurCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Assureur::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat ('dd/MM/yyyy HH:mm:ss')
            ->setDateFormat ('dd/MM/yyyy')
            ->setPaginatorPageSize(30)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Assureur")
            ->setEntityLabelInPlural("Assureurs")
            ->setPageTitle("index", "Liste d'assureurs")
            // ...
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            //IdField::new('id'),
            TextField::new('nom', 'Nom'),
            TextField::new('adresse', 'Adresse physique'),
            TelephoneField::new('telephone', 'Téléphone'),
            EmailField::new('email', 'E-mail'),
            BooleanField::new('isreassureur', 'Réassureur'),
            UrlField::new('siteweb', 'Site Internet'),
            TextField::new('rccm', 'RCCM')->hideOnIndex(),
            TextField::new('idnat', 'Id. Nationale')->hideOnIndex(),
            TextField::new('licence', 'N° Licence'),
            TextField::new('numimpot', 'N° Impôt')->hideOnIndex(),
            DateTimeField::new('updated_at', 'Last update')->hideOnform()
        ];
    }
   
}
