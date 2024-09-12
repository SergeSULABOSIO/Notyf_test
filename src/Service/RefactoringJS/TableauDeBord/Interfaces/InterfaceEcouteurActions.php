<?php

namespace App\Service\RefactoringJS\TableauDeBord\Interfaces;

use App\Service\RefactoringJS\TableauDeBord\Concrets\EvenementIndicateur;

interface InterfaceEcouteurActions
{
    public function onAfterUpdate(callable $callback);
    public function onBeforeUpdated(callable $callback);
    public function onUpdating(callable $callback);
}
