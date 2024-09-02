<?php

namespace App\Service\RefactoringJS\JSUIComponents\Tranche;

use App\Service\ServiceTaxes;
use App\Entity\CompteBancaire;
use App\Service\ServiceMonnaie;
use Doctrine\ORM\EntityManager;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSChamp;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSPanelRenderer;
use App\Service\RefactoringJS\JSUIComponents\JSUIParametres\JSCssHtmlDecoration;

class CompteBancaireFormRenderer extends JSPanelRenderer
{
    public function __construct(
        private EntityManager $entityManager,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceTaxes $serviceTaxes,
        string $pageName,
        private $objetInstance,
        $crud,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        parent::__construct(self::TYPE_FORMULAIRE, $pageName, $objetInstance, $crud, $adminUrlGenerator);
    }

    public function design()
    {
        $column = 12;
        if ($this->objetInstance instanceof CompteBancaire) {
            $column = 10;
        }
        //Section - Principale
        $this->addChamp(
            (new JSChamp())
                ->createSection(' Informations générales')
                ->setIcon('fa-solid fa-piggy-bank') //<i class="fa-solid fa-piggy-bank"></i>
                ->setHelp("Votre compte bancaire tout simplement.")
                ->setColumns($column)
                ->getChamp()
        );
        //Intitulé du compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte("intitule", "Intitulé")
                ->setColumns($column)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Numéro du compte
        $this->addChamp(
            (new JSChamp())
                ->createTexte("numero", "Numéro du compte")
                ->setColumns($column)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Banque
        $this->addChamp(
            (new JSChamp())
                ->createTexte("banque", "Banque")
                ->setColumns($column)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Code Swift
        $this->addChamp(
            (new JSChamp())
                ->createTexte("codeSwift", "Code Swift")
                ->setColumns($column)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
                        /** @var JSCssHtmlDecoration */
                        $formatedHtml = (new JSCssHtmlDecoration("span", $value))
                            ->ajouterClasseCss($this->css_class_bage_ordinaire)
                            ->outputHtml();
                        return $formatedHtml;
                    }
                )
                ->getChamp()
        );

        //Code monnaie
        $this->addChamp(
            (new JSChamp())
                ->createTexte("codeMonnaie", "Devise")
                ->setColumns($column)
                ->setFormatValue(
                    function ($value, CompteBancaire $objet) {
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
