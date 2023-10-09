<?php

namespace App\DataFixtures;

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
    private $clients;


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
        foreach ($this->produits as $produit) {
            
        }
        dd($this->produits);
    }
}
