<?php

namespace App\Service\RefactoringJS\JSUIComponents\Police;

use App\Entity\Monnaie;
use App\Service\ServiceTaxes;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use App\Controller\Admin\MonnaieCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Police;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class PoliceListeRenderer extends JSPanelRenderer
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
        parent::__construct(self::TYPE_LISTE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    
    public function design()
    {
        //Type Avenant
        $this->addChamp(
            (new JSChamp())
                ->createTexte('typeavenant', "Type d'avenant")
                ->getChamp()
        );
        //Reference
        $this->addChamp(
            (new JSChamp())
                ->createTexte('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
                ->getChamp()
        );
        //Date effet
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Date expiration
        $this->addChamp(
            (new JSChamp())
                ->createDate('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
        //Assureur
        $this->addChamp(
            (new JSChamp())
                ->createTexte('assureur', "Assureur")
                ->getChamp()
        );
        //Client
        $this->addChamp(
            (new JSChamp())
                ->createTexte('client', "Assuré (client)")
                ->getChamp()
        );
        //Produit
        $this->addChamp(
            (new JSChamp())
                ->createTexte('produit', "Couverture")
                ->getChamp()
        );
        //Prime totale
        $this->addChamp(
            (new JSChamp())
                ->createArgent('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setFormatValue(
                    function ($value, Police $objet) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($objet->getPrimeTotale());
                    }
                )
                ->getChamp()
        );
        //Gestionnaire de compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                ->getChamp()
        );
        //Assistant Gestionnaire de compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte('assistant', PreferenceCrudController::PREF_PRO_POLICE_ASSISTANT)
                ->getChamp()
        );
        //Date modification
        $this->addChamp(
            (new JSChamp())
                ->createDate('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
                ->setFormatValue(
                    function ($value, Police $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );
    }

    public function batchActions(?array $champs, ?string $type = null, ?string $pageName = null, $objetInstance = null, ?Crud $crud = null, ?AdminUrlGenerator $adminUrlGenerator = null): ?array
    {
        return $champs;
    }
}
