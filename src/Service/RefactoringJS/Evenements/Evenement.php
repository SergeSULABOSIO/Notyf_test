<?php

namespace App\Service\RefactoringJS\Evenements;

use DateTimeImmutable;

interface Evenement
{
    //Type d'évènement pour attributs des entités
    public const TYPE_ATTRIBUT_AJOUT = 0;
    public const TYPE_ATTRIBUT_EDITION = 1;
    public const TYPE_ATTRIBUT_SUPPRESSION = 2;
    //Type d'évènement pour entités elles-mêmes
    public const TYPE_ENTITE_AVANT_ENREGISTREMENT = 3;
    public const TYPE_ENTITE_AVANT_EDITION = 4;
    public const TYPE_ENTITE_AVANT_SUPPRESSION = 5;
    public const TYPE_ENTITE_APRES_ENREGISTREMENT = 3;
    public const TYPE_ENTITE_APRES_EDITION = 4;
    public const TYPE_ENTITE_APRES_SUPPRESSION = 5;
    public const TYPE_ENTITE_APRES_CHARGEMENT = 6;

    //Champs données
    public const CHAMP_DATE = "Date";
    public const CHAMP_UTILISATEUR = "Utilisateur";
    public const CHAMP_ENTREPRISE = "Entreprise";
    public const CHAMP_DONNEE = "Données";
    public const CHAMP_NEW_VALUE = "New_Value";
    public const CHAMP_OLD_VALUE = "Old_Value";
    public const CHAMP_MESSAGE = "Message";
    public const FORMAT_VALUE_PRIMITIVE = "Primitive_value";
    public const FORMAT_VALUE_ENTITY = "Entity_value";

    public function getValueFormat():?string;
    public function setValueFormat(?string $valueFormat);
    public function setType(?int $typeEvenement);
    public function getType():?int;
    public function getDonnees():?array;
    public function setDonnees(?array $tabDonnees = [
        self::CHAMP_DATE => new DateTimeImmutable("now"),
        self::CHAMP_UTILISATEUR => null,
        self::CHAMP_ENTREPRISE => null,
        self::CHAMP_DONNEE => null,
        self::CHAMP_OLD_VALUE => null,
        self::CHAMP_NEW_VALUE => null,
        self::CHAMP_MESSAGE => ""
    ]);
}
