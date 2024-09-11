<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;


interface InterfaceEcouteurActions
{
    public function onAfterUpdate();
    public function onBeforeUpdated();
    public function onUpdating();
}
