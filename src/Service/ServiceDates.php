<?php

namespace App\Service;

use App\Entity\Cotation;
use DateTime;
use DateInterval;
use App\Entity\Police;
use App\Entity\Revenu;
use DateTimeImmutable;
use App\Entity\Tranche;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Date;

class ServiceDates
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function ajouterJours(DateTimeImmutable $dateInitiale, $nbJours): DateTimeImmutable
    {
        $txt = "P" . $nbJours . "D";
        $copie = clone $dateInitiale;
        return $copie->add(new DateInterval($txt));
    }

    public function ajouterAnnees(DateTimeImmutable $dateInitiale, $nbAnnee): DateTimeImmutable
    {
        $txt = "P" . $nbAnnee . "Y";
        $copie = clone $dateInitiale;
        return $copie->add(new DateInterval($txt));
    }

    public function ajouterMinutes(DateTime $dateInitiale, $nbMinutes): DateTime
    {
        $txt = "PT" . $nbMinutes . "M";
        $copie = clone $dateInitiale;
        return $copie->add(new DateInterval($txt));
    }

    public function getTexte(DateTimeImmutable $date): string
    {
        return $date->format('d/m/Y à H:i');
    }

    public function getTexteSimple(DateTimeImmutable $date): string
    {
        return $date->format('d/m/Y');
    }

    public function aujourdhui(): DateTimeImmutable
    {
        return new \DateTimeImmutable("now");
    }

    public function dansUneAnnee(): DateTimeImmutable
    {
        return new \DateTimeImmutable("+365 days");
    }

    public function hier(): DateTimeImmutable
    {
        return new \DateTimeImmutable("-1 days");
    }

    public function demain(): DateTimeImmutable
    {
        return new \DateTimeImmutable("+1 days");
    }

    public function dansUneSemaine(): DateTimeImmutable
    {
        return new \DateTimeImmutable("+7 days");
    }


    //fonction pour gestion des tranches
    public function ajusterPeriodesPourTranches_et_Revenus(?Police $police)
    {
        if ($police != null) {
            /** @var Tranche */
            foreach ($police->getTranches() as $trancheEncours) {
                $indiceCourant = ($police->getTranches()->indexOf($trancheEncours));
                $dureesPrecedantes = $this->getTotalDureeTranchesPrecedantes($police, $indiceCourant);
                $startedAt = $police->getDateeffet()->add(new DateInterval("P" . ($dureesPrecedantes) . "M"));
                $endedAt = $startedAt->add(new DateInterval("P" . ($trancheEncours->getDuree()) . "M"));
                $endedAt = $endedAt->modify("-1 day");
                $trancheEncours->setStartedAt($startedAt);
                $trancheEncours->setEndedAt($endedAt);
                $trancheEncours->setDateEffet($police->getDateeffet());
                $trancheEncours->setDateExpiration($police->getDateexpiration());
                $trancheEncours->setDateOperation($police->getDateoperation());
                $trancheEncours->setDateEmition($police->getDateemission());
                //on enregistre les changements dans la base de données
                $this->entityManager->persist($trancheEncours);
                $this->entityManager->flush();
            }

            /** @var Revenu */
            foreach ($police->getRevenus() as $revenuEncours) {
                $revenuEncours->setDateEffet($police->getDateeffet());
                $revenuEncours->setDateExpiration($police->getDateexpiration());
                $revenuEncours->setDateOperation($police->getDateoperation());
                $revenuEncours->setDateEmition($police->getDateemission());
                //on enregistre les changements dans la base de données
                $this->entityManager->persist($revenuEncours);
                $this->entityManager->flush();
            }
        }
    }

    public function detruirePeriodesPourTranches_et_Revenus(?Cotation $cotation)
    {
        if ($cotation != null) {
            /** @var Tranche */
            foreach ($cotation->getTranches() as $trancheEncours) {
                $trancheEncours->setStartedAt(null);
                $trancheEncours->setEndedAt(null);
                $trancheEncours->setDateEffet(null);
                $trancheEncours->setDateExpiration(null);
                $trancheEncours->setDateOperation(null);
                $trancheEncours->setDateEmition(null);
                //on enregistre les changements dans la base de données
                $this->entityManager->persist($trancheEncours);
                $this->entityManager->flush();
            }

            /** @var Revenu */
            foreach ($cotation->getRevenus() as $revenuEncours) {
                $revenuEncours->setDateEffet(null);
                $revenuEncours->setDateExpiration(null);
                $revenuEncours->setDateOperation(null);
                $revenuEncours->setDateEmition(null);
                //on enregistre les changements dans la base de données
                $this->entityManager->persist($revenuEncours);
                $this->entityManager->flush();
            }
        }
    }

    private function getTotalDureeTranchesPrecedantes(?Police $police, $indiceCourant)
    {
        $totalDureesCumulees = 0;
        /** @var Tranche */
        foreach ($police->getTranches() as $tranche) {
            if ($police->getTranches()->indexOf($tranche) < $indiceCourant) {
                $totalDureesCumulees = $totalDureesCumulees + $tranche->getDuree();
            }
        }
        return $totalDureesCumulees;
    }
}
