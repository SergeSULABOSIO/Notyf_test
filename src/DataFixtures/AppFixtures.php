<?php

namespace App\DataFixtures;

use App\Controller\Admin\CotationCrudController;
use App\Controller\Admin\PoliceCrudController;
use Faker\Factory;
use App\Entity\Taxe;
use App\Entity\Piste;
use App\Entity\Action;
use App\Entity\Client;
use App\Entity\Expert;
use App\Entity\Article;
use App\Entity\Contact;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\Victime;
use App\Entity\Assureur;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\EtapeCrm;
use App\Entity\ActionCRM;
use App\Entity\Automobile;
use App\Entity\Entreprise;
use App\Entity\Partenaire;
use App\Entity\DocClasseur;
use App\Entity\EntreeStock;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Entity\DocCategorie;
use App\Entity\EtapeSinistre;
use App\Service\ServiceEntreprise;
use App\Entity\CommentaireSinistre;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Controller\Admin\UtilisateurCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $produits;
    private $etapes;
    private $clients;
    private $assureurs;


    public const FIXTURES_PRODUCTION_GENERER_POLICES = "production-generer-police";

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise
        )
    {
    }

    public function load(ObjectManager $manager): void
    {
        // use the factory to create a Faker\Generator instance
        $faker = Factory::create();

        //Chargement des produits
        $this->produits = $this->entityManager->getRepository(Produit::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        //Chargement des etapes
        $this->etapes = $this->entityManager->getRepository(EtapeCrm::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        //Chargement des assureurs
        $this->assureurs = $this->entityManager->getRepository(Assureur::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
        //Chargement des clients
        $this->clients = $this->entityManager->getRepository(Client::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );


        //On charge la dernière étape d'une piste dans le CRM
        $dernireEtapeCrm = $this->etapes[count($this->etapes)-1];
        $dernierAssureur = $this->assureurs[0];

        
        $client = new Client();
        $client->setNom("Test Client - " . $faker->company());
        $client->setAdresse("18c, Av. Moanda, Q. Matonge, C. KALAMU, Kinshasa / RDC");
        $client->setEmail("ssula@aib-brokers.com");
        $client->setIdnat("IDNAT" . $faker->randomNumber(5, false));
        $client->setIspersonnemorale(true);
        $client->setNumipot("IMPOT" . $faker->randomNumber(5, false));
        $client->setRccm("RCCM" . $faker->randomNumber(5, false));
        $client->setSecteur(0);
        $client->setSiteweb("www.aib-brokers.com");
        $client->setTelephone("+243828727706");
        $client->setUtilisateur($this->serviceEntreprise->getUtilisateur());
        $client->setEntreprise($this->serviceEntreprise->getEntreprise());
        $client->setCreatedAt(new \DateTimeImmutable());
        $client->setUpdatedAt(new \DateTimeImmutable());
        
        $manager->persist($client);

        $manager->flush();
    }
}
