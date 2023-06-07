<?php

namespace App\Service;

use App\Entity\DocCategorie;
use App\Entity\DocClasseur;
use App\Entity\EtapeCrm;
use App\Entity\EtapeSinistre;
use App\Entity\Monnaie;
use Faker\Factory;
use App\Entity\Preference;
use App\Entity\Produit;
use App\Entity\Taxe;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ServiceIngredients
{
    //public const PAREMETRE_UTILISATEUR = 25;
    //public const PAREMETRE_ENTREPRISE = 26;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServicePreferences $servicePreferences
    ) {
    }

    public function creerIngredients($utilisateur, $entreprise)
    {

        $faker = Factory::create();

        $taMonnaies = [
            [
                "setCode" => "USD",
                "setNom" => "Dollar Américain",
                "setTauxusd" => 1,
                "setIslocale" => true
            ],
            [
                "setCode" => "EUR",
                "setNom" => "Euro",
                "setTauxusd" => 1.09,
                "setIslocale" => false
            ]
        ]; //array("USD", "CDF");

        $tabTaxes = [
            [
                "setNom" => "TVA",
                "setDescription" => "Taxe sur la Valeur Ajoutée",
                "setTaux" => 0.16,
                "setPayableparcourtier" => false,
                "setOrganisation" => "DGI - Direction Générale des Impôts."
            ],
            [
                "setNom" => "ARCA",
                "setDescription" => "Frais de surveillance",
                "setTaux" => 0.02,
                "setPayableparcourtier" => true,
                "setOrganisation" => "ARCA - Autorité de Régulation et de Contrôle des Assurances."
            ]
        ]; //array("TVA", "ARCA");

        $tabEtapesCRM = [
            "PROSPECTION",
            "PRODUCTION DE COTATION",
            "EMISSION DE LA POLICE",
            "RENOUVELLEMENT"
        ];

        $tabProduits = [
            [
                "setNom" => "VIE ET EPARGNE / LIFE",
                "setCode" => "VIE",
                "setDescription" => "L'assurance vie est un contrat par lequel l'assureur s'engage, en contrepartie du paiement de primes, à verser une rente ou un capital à l'assuré ou à ses bénéficiaires.",
                "setIsobligatoire" => false,
                "setTauxarca" => 0.10,
                "setIsabonnement" => false,
                "setCategorie" => 1
            ],
            [
                "setNom" => "INCENDIE ET RISQUES DIVERS / ASSET / FAP",
                "setCode" => "IMR",
                "setDescription" => "Une assurance incendie est avant tout une {assurance de choses}. Ce qui veut dire qu'elle indemnise les dommages causés à vos biens matériels, plus particulièrement à l'habitation et son contenu. Mais elle couvre dans certaines circonstances également votre responsabilité civile à l'égard d'autrui.",
                "setIsobligatoire" => true,
                "setTauxarca" => 0.10,
                "setIsabonnement" => false,
                "setCategorie" => 0
            ],
            [
                "setNom" => "RC AUTOMOBILE / MOTOR TPL",
                "setCode" => "RCA",
                "setDescription" => "La garantie responsabilité civile de votre assurance automobile couvre les dommages causés aux tiers par vous ou par les personnes vivant avec vous (enfants, concubin, époux....).",
                "setIsobligatoire" => true,
                "setTauxarca" => 0.10,
                "setIsabonnement" => false,
                "setCategorie" => 0
            ],
            [
                "setNom" => "TOUS RISQUES AUTOMOBILES / MOTOR COMP.",
                "setCode" => "TRA",
                "setDescription" => "La garantie tous risques vous permet d'être indemnisé pour tous les dommages subis par votre véhicule, quel que soit le type d'accident et quelle que soit votre responsabilité en tant que conducteur.",
                "setIsobligatoire" => false,
                "setTauxarca" => 0.15,
                "setIsabonnement" => false,
                "setCategorie" => 0
            ]
        ];

        $tabSinistres = [
            [
                "setNom" => "OUVERTURE",
                "setDescription" => "La toute prémière étape où l'assureur est notifié de la survénance d'un probable sinistre. La déclaration doit se faire dans le délais contractuel.",
                "setIndice" => 0
            ],
            [
                "setNom" => "COLLECTE DES DONNEES",
                "setDescription" => "L'assureur (ou l'espert désigné par celui-ci) éffectue la collecte d'information permettant de mieux comprendre les circonstances de l'incident et de quantifier les dégâts.",
                "setIndice" => 1
            ],
            [
                "setNom" => "EVALUATION DES DEGATS",
                "setDescription" => "Analyse de données, évaluation et détermination de la somme compensatoire éventuelle à verser à la victime.",
                "setIndice" => 2
            ],
            [
                "setNom" => "INDEMNISATION ET / OU CLOTURE",
                "setDescription" => "En cas de sinistre approvée conformément à la police, l'assuereur effectue le règlement compensatoire et clos le dossier. Au cas contraire, l'assureur informe à l'assuré les raisons du rejet et clos tout de même le dossier.",
                "setIndice" => 3
            ]
        ];

        $tabBiblioCategorie = [
            "POLICES D'ASSURANCE",
            "FORMULAIRES DE PROPOSITION",
            "MANDATS DE COURTAGE",
            "FACTURES"
        ];

        $tabBiblioClasseur = [
            "PRODUCTION",
            "SINISTRES",
            "FINANCES"
        ];

        //Construction des objets et persistance
        //MONNAIES
        foreach ($taMonnaies as $O_monnaie) {
            $monnaie = new Monnaie();
            $monnaie->setCode($O_monnaie['setCode']);
            $monnaie->setNom($O_monnaie['setNom']);
            $monnaie->setTauxusd($O_monnaie['setTauxusd']);
            $monnaie->setIslocale($O_monnaie['setIslocale']);

            $monnaie->setEntreprise($entreprise);
            $monnaie->setCreatedAt(new \DateTimeImmutable());
            $monnaie->setUpdatedAt(new \DateTimeImmutable());
            $monnaie->setUtilisateur($utilisateur);
            //persistance
            $this->entityManager->persist($monnaie);
        }

        //TAXES
        foreach ($tabTaxes as $O_taxes) {
            $taxe = new Taxe();
            $taxe->setNom($O_taxes['setNom']);
            $taxe->setDescription($O_taxes['setDescription']);
            $taxe->setTaux($O_taxes['setTaux']);
            $taxe->setPayableparcourtier($O_taxes['setPayableparcourtier']);
            $taxe->setOrganisation($O_taxes['setOrganisation']);

            $taxe->setEntreprise($entreprise);
            $taxe->setCreatedAt(new \DateTimeImmutable());
            $taxe->setUpdatedAt(new \DateTimeImmutable());
            $taxe->setUtilisateur($utilisateur);

            $this->entityManager->persist($taxe);
        }

        //ETAPE CRM
        foreach ($tabEtapesCRM as $nomEtape) {
            $etapeCRM = new EtapeCrm();
            $etapeCRM->setNom($nomEtape);

            $etapeCRM->setEntreprise($entreprise);
            $etapeCRM->setCreatedAt(new \DateTimeImmutable());
            $etapeCRM->setUpdatedAt(new \DateTimeImmutable());
            $etapeCRM->setUtilisateur($utilisateur);
            //persistance
            $this->entityManager->persist($etapeCRM);
        }

        //PRODUIT
        foreach ($tabProduits as $O_produit) {
            $produit = new Produit();
            $produit->setNom($O_produit['setNom']);
            $produit->setCode($O_produit['setCode']);
            $produit->setDescription($O_produit['setDescription']);
            $produit->setIsobligatoire($O_produit['setIsobligatoire']);
            $produit->setTauxarca($O_produit['setTauxarca']);
            $produit->setIsabonnement($O_produit['setIsabonnement']);
            $produit->setCategorie($O_produit['setCategorie']);

            $produit->setEntreprise($entreprise);
            $produit->setCreatedAt(new \DateTimeImmutable());
            $produit->setUpdatedAt(new \DateTimeImmutable());
            $produit->setUtilisateur($utilisateur);
            //persistance
            $this->entityManager->persist($produit);
            //$this->manager->flush();
        }

        //ETAPE SINISTRE
        foreach ($tabSinistres as $O_etape) {
            $etapeSinistre = new EtapeSinistre();
            $etapeSinistre->setNom($O_etape['setNom']);
            $etapeSinistre->setDescription($O_etape['setDescription']);
            $etapeSinistre->setIndice($O_etape['setIndice']); //$indice

            $etapeSinistre->setEntreprise($entreprise);
            $etapeSinistre->setCreatedAt(new \DateTimeImmutable());
            $etapeSinistre->setUpdatedAt(new \DateTimeImmutable());
            $etapeSinistre->setUtilisateur($utilisateur);
            //persistance
            $this->entityManager->persist($etapeSinistre);
        }

        //CATEGORIE BIBBLIOTHEQUE
        foreach ($tabBiblioCategorie as $O_categorie) {
            $categorieBib = new DocCategorie();
            $categorieBib->setNom($O_categorie);

            $categorieBib->setEntreprise($entreprise);
            $categorieBib->setCreatedAt(new \DateTimeImmutable());
            $categorieBib->setUpdatedAt(new \DateTimeImmutable());
            $categorieBib->setUtilisateur($utilisateur);
            //persistance
            $this->entityManager->persist($categorieBib);
        }

        //CLASSEUR BIBBLIOTHEQUE
        foreach ($tabBiblioClasseur as $O_classeur) {
            $classeurBib = new DocClasseur();
            $classeurBib->setNom($O_classeur);

            $classeurBib->setEntreprise($entreprise);
            $classeurBib->setCreatedAt(new \DateTimeImmutable());
            $classeurBib->setUpdatedAt(new \DateTimeImmutable());
            $classeurBib->setUtilisateur($utilisateur);
            //persistance
            $this->entityManager->persist($classeurBib);
        }
        $this->entityManager->flush();
        //On crée les préférences par défaut de l'utilisateur
        $this->servicePreferences->creerPreference($utilisateur, $entreprise);
    }
}
