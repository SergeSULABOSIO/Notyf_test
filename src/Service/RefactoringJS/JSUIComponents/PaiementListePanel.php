<?php

namespace App\Service\RefactoringJS\JSUIComponents;

use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;


class PaiementListePanel implements JSPanel
{
    private ?int $type;
    private ?array $champsPanel = [];

    public function __construct()
    {
        //On construit le panel
        $this->init();
        $this->appliquerType();
    }

    private function  appliquerType()
    {
        foreach ($this->champsPanel as $champ) {
            switch ($this->type) {
                case self::TYPE_LISTE:
                    $champ->onlyOnIndex();
                    break;
                case self::TYPE_DETAILS:
                    $champ->onlyOnDetail();
                    break;
                case self::TYPE_FORMULAIRE:
                    $champ->onlyOnForms();
                    break;

                default:
                    # code...
                    break;
            }
        }
    }

    public function setType(?int $type)
    {
        $this->type = $type;
    }

    public function addSection(?string $titre, ?string $icone, ?int $colonne)
    {
        $this->champsPanel[] = FormField::addPanel($titre)
            ->setIcon($icone)
            ->setColumns($colonne);
    }

    public function addOnglet(?string $titre, ?string $icone, ?string $helpMessage)
    {
        $this->champsPanel[] = [
            FormField::addTab(' ' . $titre)
                ->setIcon($icone) //<i class="fa-sharp fa-solid fa-address-book"></i>
                ->setHelp($helpMessage),
        ];
    }

    public function init()
    {
        //initialisation ici dedans
        $this->setType(self::TYPE_LISTE);

        // $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_PAIEMENT_ID)
        //         ->onlyOnIndex();
    }



    public function reset()
    {
        $this->champsPanel = [];
    }

    public function getChamps(): ?array
    {
        return $this->champsPanel;
    }
}
