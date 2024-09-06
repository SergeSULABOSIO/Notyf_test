<?php

namespace App\Service\RefactoringJS\JSUIComponents\Sinistre;

use App\Entity\Sinistre;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\ServiceEntreprise;

class SinistreFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private ServiceEntreprise $serviceEntreprise,
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
        if ($this->objetInstance instanceof Sinistre) {
            $column = 10;
        }
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection("Informations générales")
                ->setIcon('fas fa-bell') //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp("Evènement(s) malheureux pouvant déclancher le processus d'indemnisation selon les termes de la police.")
                ->setColumns($column)
                ->getChamp()
        );
        //Titre
        $this->addChamp(
            (new JSChamp())
                ->createTexte('titre', PreferenceCrudController::PREF_SIN_SINISTRE_ITITRE)
                ->setColumns($column)
                ->getChamp()
        );
        //Référence
        $this->addChamp(
            (new JSChamp())
                ->createTexte('numero', PreferenceCrudController::PREF_SIN_SINISTRE_REFERENCE)
                ->setFormatValue(function ($value, Sinistre $sinistre) {
                    return $value;
                })
                ->setColumns($column)
                ->getChamp()
        );
        //Etape
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('etape', PreferenceCrudController::PREF_SIN_SINISTRE_ETAPE)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->getChamp()
        );
        //Coût
        $this->addChamp(
            (new JSChamp())
                ->createArgent('cout', PreferenceCrudController::PREF_SIN_SINISTRE_COUT)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setColumns($column)
                ->getChamp()
        );
        //Date d'occurrence
        $this->addChamp(
            (new JSChamp())
                ->createDate('occuredAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_OCCURENCE)
                ->setColumns($column)
                ->getChamp()
        );
        //Experts
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('experts', PreferenceCrudController::PREF_SIN_SINISTRE_EXPERT)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->getChamp()
        );
        //Victimes
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('victimes', PreferenceCrudController::PREF_SIN_SINISTRE_VICTIMES)
                ->setHelp("Si la victime ne se trouve pas dans cette liste, ne vous inquiètez pas car vous pouvez en ajouter à tout moment après l'enregistrement de ce sinistre.")
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->getChamp()
        );
        //Polices
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('police', PreferenceCrudController::PREF_SIN_SINISTRE_POLICE)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->getChamp()
        );
        //Description
        $this->addChamp(
            (new JSChamp())
                ->createEditeurTexte('description', PreferenceCrudController::PREF_SIN_SINISTRE_DESCRIPTION)
                ->setColumns($column)
                ->getChamp()
        );
        //Montant payé
        $this->addChamp(
            (new JSChamp())
                ->createArgent('montantPaye', PreferenceCrudController::PREF_SIN_SINISTRE_MONTANT_PAYE)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setColumns($column)
                ->getChamp()
        );
        //Date de paiement / d'indemnisation
        $this->addChamp(
            (new JSChamp())
                ->createDate('paidAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_PAIEMENT)
                ->setColumns($column)
                ->getChamp()
        );
        //Documents
        $this->addChamp(
            (new JSChamp())
                ->createAssociation('docPieces', PreferenceCrudController::PREF_SIN_SINISTRE_DOCUMENTS)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns($column)
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
