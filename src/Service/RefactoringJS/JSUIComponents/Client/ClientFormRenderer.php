<?php

namespace App\Service\RefactoringJS\JSUIComponents\Client;

use App\Entity\Assureur;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\ClientCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class ClientFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        private string $pageName,
        private $objetInstance,
        private $crud,
        private AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof Client) {
            $column = 10;
        }

        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection(' Informations générales')
                ->setIcon('fas fa-person-shelter') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Le client c'est l'assuré ou le bénéficiaire de la couverture d'assurance.")
                ->setColumns($column)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_PRO_CLIENT_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //Adresse
        $this->addChamp(
            (new JSChamp())
                ->createTexte('adresse', PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE)
                ->setColumns($column)
                ->getChamp()
        );
        //Téléphone
        $this->addChamp(
            (new JSChamp())
                ->createTexte('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)
                ->setColumns($column)
                ->getChamp()
        );
        //Email
        $this->addChamp(
            (new JSChamp())
                ->createTexte('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)
                ->setColumns($column)
                ->getChamp()
        );
        //Site web
        $this->addChamp(
            (new JSChamp())
                ->createTexte('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)
                ->setColumns($column)
                ->getChamp()
        );
        //Personne morale?
        $this->addChamp(
            (new JSChamp())
                ->createChoix('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
                ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE)
                ->setColumns($column)
                ->getChamp()
        );
        //Exoneré de la Taxe?
        $this->addChamp(
            (new JSChamp())
                ->createBoolean('exoneree', "Exonéré")
                ->setColumns($column)
                ->getChamp()
        );
        //RCCM
        $this->addChamp(
            (new JSChamp())
                ->createTexte('rccm', PreferenceCrudController::PREF_PRO_CLIENT_RCCM)
                ->setColumns($column)
                ->getChamp()
        );
        //Idnat
        $this->addChamp(
            (new JSChamp())
                ->createTexte('idnat', PreferenceCrudController::PREF_PRO_CLIENT_IDNAT)
                ->setColumns($column)
                ->getChamp()
        );
        //Num Impot
        $this->addChamp(
            (new JSChamp())
                ->createTexte('numipot', PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT)
                ->setColumns($column)
                ->getChamp()
        );
        //Secteur
        $this->addChamp(
            (new JSChamp())
                ->createChoix('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
                ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR)
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
