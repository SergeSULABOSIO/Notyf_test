<?php

namespace App\Service;

use DateTimeImmutable;
use App\Entity\Entreprise;
use App\Entity\Preference;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\EtapeCrm;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ServicePreferences
{
    private $preferences;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise
    ) {
    }

    public function chargerPreference(Utilisateur $utilisateur, Entreprise $entreprise): Preference
    {
        $preferences = $this->entityManager->getRepository(Preference::class)->findBy(
            [
                'entreprise' => $entreprise,
                'utilisateur' => $utilisateur,
            ]
        );
        return $preferences[0];
    }

    public function appliquerPreferenceApparence(Dashboard $dashboard, Utilisateur $utilisateur, Entreprise $entreprise)
    {
        $preference = $this->chargerPreference($utilisateur, $entreprise);
        if ($preference->getApparence() == 0) {
            $dashboard->disableDarkMode();
        }
    }

    public function appliquerPreferenceTaille($instance, Crud $crud)
    {
        $preference = $this->chargerPreference($this->serviceEntreprise->getUtilisateur(), $this->serviceEntreprise->getEntreprise());
        if($instance instanceof EtapeCrm){
            $taille = 100;
            if($preference->getCrmTaille() != 0){
                $taille = $preference->getCrmTaille();
            }
            $crud->setPaginatorPageSize($taille);
        }
    }

    public function canShow(array $tab, $indice_attribut)
    {
        foreach ($tab as $valeur) {
            if ($valeur == $indice_attribut) {
                return true;
            }
        }
        return false;
    }

    public function appliquerPreferenceAttributs($tabAttributs)
    {
        $preference = $this->chargerPreference($this->serviceEntreprise->getUtilisateur(), $this->serviceEntreprise->getEntreprise());
        //dd($this->canShow($this->preferences[0]->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_NOM]));//$this->preferences[0]->getCrmEtapes()

        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_ETAPES_ID)->onlyOnIndex();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)->onlyOnIndex();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR)->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE)->onlyOnIndex();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION)->onlyOnIndex();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION)->onlyOnIndex();
        }

        /* 
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR)
            ->setColumns(6)->hideOnForm()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);

        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION)
            ->hideOnIndex()
            ->hideOnForm();

        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION)
            ->hideOnForm();

        $tabAttributs[] = AssociationField::new('entreprise', "Entreprise")
            ->hideOnIndex()
            ->setColumns(6); */

        //dd($tabAttributs);
        return $tabAttributs;
    }

    public function creerPreference($utilisateur, $entreprise)
    {
        $preference = new Preference();
        $preference->setApparence(0);
        $preference->setUtilisateur($utilisateur);
        $preference->setEntreprise($entreprise);
        $preference->setCreatedAt(new DateTimeImmutable());
        $preference->setUpdatedAt(new DateTimeImmutable());
        //CRM
        $preference->setCrmTaille(100);
        $preference->setCrmMissions([0, 1]);
        $preference->setCrmFeedbacks([0, 1]);
        $preference->setCrmCotations([0, 1]);
        $preference->setCrmEtapes([0, 1]);
        $preference->setCrmPistes([0, 1]);
        //PRO
        $preference->setProTaille(100);
        $preference->setProAssureurs([0, 1]);
        $preference->setProAutomobiles([0, 1]);
        $preference->setProContacts([0, 1]);
        $preference->setProClients([0, 1]);
        $preference->setProPartenaires([0, 1]);
        $preference->setProPolices([0, 1]);
        $preference->setProProduits([0, 1]);
        //FIN
        $preference->setFinTaille(100);
        $preference->setFinTaxes([0, 1]);
        $preference->setFinMonnaies([0, 1]);
        $preference->setFinCommissionsPayees([0, 1]);
        $preference->setFinRetrocommissionsPayees([0, 1]);
        $preference->setFinTaxesPayees([0, 1]);
        //SIN
        $preference->setSinTaille(100);
        $preference->setSinCommentaires([0, 1]);
        $preference->setSinEtapes([0, 1]);
        $preference->setSinSinistres([0, 1]);
        $preference->setSinVictimes([0, 1]);
        //BIB
        $preference->setBibTaille(100);
        $preference->setBibCategories([0, 1]);
        $preference->setBibClasseurs([0, 1]);
        $preference->setBibPieces([0, 1]);
        //PAR
        $preference->setParTaille(100);
        $preference->setParUtilisateurs([0, 1]);

        //persistance
        $this->entityManager->persist($preference);
        $this->entityManager->flush();
    }
}
