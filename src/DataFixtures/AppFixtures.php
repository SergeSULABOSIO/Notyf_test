<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\EntreeStock;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        // use the factory to create a Faker\Generator instance
        $faker = Factory::create();
        //On va charger 20 produits automatiquement dans la base de données
        for ($i = 1; $i < 20; $i++) {
            $article = new Article();
            $article->setCode(substr($faker->ean13(), 4));
            $article->setNom($faker->company(10));
            $article->setPrix(9.99);
            $article->setDescription("Un savon de lux et de très bonne qualité.");
            //On persiste dans la base de données
            $manager->persist($article);
            $manager->flush();
            //Test = OK

            //On le charge dans le stock
            $entree_stock = new EntreeStock();
            $entree_stock->setQuantite($faker->randomDigitNotNull());
            $entree_stock->setPrixUnitaire($faker->randomFloat(2, 100, 5000));
            $entree_stock->setDate(new \DateTimeImmutable());
            $entree_stock->setArticle($article);

            //On persiste dans la base de données
            $manager->persist($entree_stock);
            $manager->flush();
        }

        $user_admin = new Utilisateur();
        $user_admin->setNom("Admin_" . $faker->name());
        $user_admin->setPseudo("ADM" . mt_rand(0, 1) . "PS");
        $user_admin->setEmail('admin@gmail.com');
        $user_admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $hashedPassword = $this->hasher->hashPassword($user_admin, "admin");
        $user_admin->setPassword($hashedPassword);

        //On persiste dans la base de données
        $manager->persist($user_admin);
        $manager->flush();

        for ($i = 0; $i < 10; $i++) {
            $user = new Utilisateur();
            $user->setNom($faker->name());
            $user->setPseudo(mt_rand(0, 1) . "PS");
            $user->setEmail($faker->email());
            $user->setRoles(['ROLE_USER']);

            $hashedPassword = $this->hasher->hashPassword($user, "password");
            $user->setPassword($hashedPassword);

            //On persiste dans la base de données
            $manager->persist($user);
            $manager->flush();
        }
    }
}
