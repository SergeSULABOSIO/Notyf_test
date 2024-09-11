<?php
namespace App\Service\RefactoringJS\TableauDeBord;


interface InterfaceEcouteurActions
{
    public function onAfterUpdate();
    public function onBeforeUpdated();
    public function onUpdating();
}
