<?php

namespace App\Service\RefactoringJS\JSUIComponents;


interface JSPanel
{
    //Fonctions de creation du panel
    public function addPanel(?string $titre);
    public function reset();
}
