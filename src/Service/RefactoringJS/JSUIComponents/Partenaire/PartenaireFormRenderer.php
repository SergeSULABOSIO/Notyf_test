<?php

namespace App\Service\RefactoringJS\JSUIComponents\Partenaire;

use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Partenaire;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PartenaireFormRenderer extends JSPanelRenderer
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
        if ($this->objetInstance instanceof Partenaire) {
            $column = 10;
        }
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-handshake') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Le partenaire ou intermédiaire à travers lequel un client peut être acquis.")
                ->setColumns($column)
                ->getChamp()
        );
        //Nom
        $this->addChamp(
            (new JSChamp())
                ->createTexte("nom", PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)
                ->setColumns($column)
                ->getChamp()
        );
        //Part
        $this->addChamp(
            (new JSChamp())
                ->createPourcentage('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)
                ->setColumns($column)
                ->getChamp()
        );
        //Adresse
        $this->addChamp(
            (new JSChamp())
                ->createTexte('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)
                ->setColumns($column)
                ->getChamp()
        );
        //Email
        $this->addChamp(
            (new JSChamp())
                ->createTexte('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)
                ->setColumns($column)
                ->getChamp()
        );
        //Site web
        $this->addChamp(
            (new JSChamp())
                ->createTexte('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)
                ->setColumns($column)
                ->getChamp()
        );
        //RCCM
        $this->addChamp(
            (new JSChamp())
                ->createTexte('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)
                ->setColumns($column)
                ->getChamp()
        );
        //Idnat
        $this->addChamp(
            (new JSChamp())
                ->createTexte('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)
                ->setColumns($column)
                ->getChamp()
        );
        //Num Impot
        $this->addChamp(
            (new JSChamp())
                ->createTexte('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
