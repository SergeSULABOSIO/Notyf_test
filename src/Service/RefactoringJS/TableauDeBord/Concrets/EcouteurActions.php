<?php

namespace App\Service\RefactoringJS\TableauDeBord\Concrets;

use App\Service\RefactoringJS\TableauDeBord\Interfaces\InterfaceEcouteurActions;

class EcouteurActions implements InterfaceEcouteurActions
{

    public function __construct() {}

    public function onAfterUpdate(callable $callback)
    {
        $callback();
        dd("onAfterUpdate");
    }

    public function onBeforeUpdated(callable $callback)
    {
        $callback();
        dd("onBeforeUpdated");
    }

    public function onUpdating(callable $callback)
    {
        $callback();
        dd("onUpdating");
    }
}
