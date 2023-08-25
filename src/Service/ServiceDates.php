<?php

namespace App\Service;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints\Date;

class ServiceDates
{
    public function __construct()
    {
    }

    public function ajouterJours(DateTime $dateInitiale, $nbJours): DateTime{
        $txt = "P" . $nbJours . "D";
        $copie = clone $dateInitiale;
        return $copie->add(new DateInterval($txt));
    }

    public function ajouterAnnees(DateTime $dateInitiale, $nbAnnee): DateTime{
        $txt = "P" . $nbAnnee . "Y";
        $copie = clone $dateInitiale;
        return $copie->add(new DateInterval($txt));
    }

    public function ajouterMinutes(DateTime $dateInitiale, $nbMinutes): DateTime{
        $txt = "PT" . $nbMinutes . "M";
        $copie = clone $dateInitiale;
        return $copie->add(new DateInterval($txt));
    }

    public function getTexte(DateTime $date): string{
        return $date->format('d-m-Y à H:i');
    }

    public function aujourdhui(): DateTimeImmutable{
        return new \DateTimeImmutable("now");
    }

    public function dansUneAnnee(): DateTimeImmutable{
        return new \DateTimeImmutable("+365 days");
    }

    public function hier(): DateTimeImmutable{
        return new \DateTimeImmutable("-1 days");
    }

    public function demain(): DateTimeImmutable{
        return new \DateTimeImmutable("+1 days");
    }

    public function dansUneSemaine(): DateTimeImmutable{
        return new \DateTimeImmutable("+7 days");
    }
}
