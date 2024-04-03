<?php
namespace App\Service\RefactoringJS\Evenements;

use DateTimeImmutable;
use App\Service\RefactoringJS\Evenements\Evenement;

class EvenementConcret implements Evenement
{
    private ?int $type;
    private ?array $donnees;
    private ?string $valueFormat;


    public function __construct(?int $type) {
        $this->type = $type;
    }

    public function getValueFormat(): ?string
    {
        return $this->valueFormat;
    }


    public function setValueFormat(?string $valueFormat)
    {
        $this->valueFormat = $valueFormat;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $typeEvenement)
    {
        $this->type = $typeEvenement;
    }

    public function getDonnees(): ?array
    {
        return $this->donnees;
    }

    public function setDonnees(?array $tabDonnees = [
            self::CHAMP_DATE => new DateTimeImmutable("now"),
            self::CHAMP_UTILISATEUR => null,
            self::CHAMP_ENTREPRISE => null,
            self::CHAMP_DONNEE => null
        ])
    {
        $this->donnees = $tabDonnees;
    }
}
