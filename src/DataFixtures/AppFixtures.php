<?php

namespace App\DataFixtures;

use App\Controller\Admin\UtilisateurCrudController;
use App\Entity\Action;
use App\Entity\ActionCRM;
use App\Entity\Article;
use App\Entity\EntreeStock;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Assureur;
use App\Entity\Automobile;
use App\Entity\Client;
use App\Entity\CommentaireSinistre;
use App\Entity\Contact;
use App\Entity\Cotation;
use App\Entity\DocCategorie;
use App\Entity\DocClasseur;
use App\Entity\DocPiece;
use App\Entity\Entreprise;
use App\Entity\EtapeCrm;
use App\Entity\EtapeSinistre;
use App\Entity\Expert;
use App\Entity\FeedbackCRM;
use App\Entity\Monnaie;
use App\Entity\Partenaire;
use App\Entity\Piste;
use App\Entity\Produit;
use App\Entity\Taxe;
use App\Entity\Victime;

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
            $article->setCreatedAt(new \DateTimeImmutable());
            $article->setUpdatedAt(new \DateTimeImmutable());

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
            $entree_stock->setCreatedAt(new \DateTimeImmutable());
            $entree_stock->setUpdatedAt(new \DateTimeImmutable());

            //On persiste dans la base de données
            $manager->persist($entree_stock);
            $manager->flush();
        }

        $user_admin = new Utilisateur();
        $user_admin->setNom("Admin_" . $faker->name());
        $user_admin->setPseudo("ADM" . mt_rand(0, 1) . "PS");
        $user_admin->setEmail('admin@gmail.com');
        //$user_admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $user_admin->setRoles([
            //Accès aux fonctionnalités
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL],
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION],
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_FINANCES],
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_SINISTRES],
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_BIBLIOTHE],
            //Pouvoeir d'action
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION],
            //Visibilité
            UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]    
        ]);

        $hashedPassword = $this->hasher->hashPassword($user_admin, "admin");
        $user_admin->setPassword($hashedPassword);
        $user_admin->setCreatedAt(new \DateTimeImmutable());
        $user_admin->setUpdatedAt(new \DateTimeImmutable());

        //On persiste dans la base de données
        $manager->persist($user_admin);
        $manager->flush();

        for ($i = 0; $i < 10; $i++) {
            $user = new Utilisateur();
            $user->setNom($faker->name());
            $user->setPseudo(mt_rand(0, 1) . "PS");
            $user->setEmail($faker->email());
            //$user->setRoles(['ROLE_USER']);
            //$user->setRoles([UtilisateurCrudController::TAB_POSTES['CLIENT']]);
            $user->setRoles([
                //Accès aux fonctionnalités
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_COMMERCIAL],
                //Pouvoeir d'action
                //Visibilité
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_LOCALE]    
            ]);

            $hashedPassword = $this->hasher->hashPassword($user, "password");
            $user->setPassword($hashedPassword);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setUpdatedAt(new \DateTimeImmutable());

            //On persiste dans la base de données
            $manager->persist($user);
            $manager->flush();
        }



        $faker = Factory::create();

        $tabNomsPartenaires = array("AFINBRO", "BOLLORE LUSHI", "MARSH", "O'NEILS");
        $tabMarquesAutomobiles = array("TOYOTA", "NISSAN", "MAZDA", "SUZUKI", "MERCEDES");
        $tabNomsTaxes = array("TVA", "ARCA");
        $tabCodesMonnaies = array("USD", "CDF");
        $tabNomsAssureurs = array("SFA CONGO SA", "ACTIVA", "SUNU", "MAYFAIRE", "RAWSUR", "SONAS");
        $tabNomsProduits = array(
            "INCENDIE ET RISQUES DIVERS / ASSET",
            "RC AUTOMOBILE / MOTOR TPL",
            "TOUS RISQUES AUTOMOBILE / MOTOR COMP",
            "RC GENERALE OU D'EXPLOITATION / GL",
            "RC EMPLOYEUR / EMPL",
            "ACCIDENT DU TRAVAIL / GPA",
            "DEGATS MATERIELS ET PERTES D'EXPLOITATION / PDBI",
            "GLOBALE DES BANQUES / BBB",
            "TRANSPORT DES FRONDS / CIT",
            "TRANSPORT DES FACULTES / GIT",
            "RISQUES POLITIQUES ET TERRORISME / PVT"
        );

        //ENTREPRISE
        $entreprise = new Entreprise();
        $entreprise->setNom("AIB RDC Sarl");
        $entreprise->setAdresse("Avenue de la Gombe, Kinshasa / RDC");
        $entreprise->setIdnat("IDNAT00045");
        $entreprise->setNumimpot("NUMIMPO00124545");
        $entreprise->setRccm("RCCM045CDKIN");
        $entreprise->setSecteur(2);
        $entreprise->setTelephone("+243828727706");
        $entreprise->setCreatedAt(new \DateTimeImmutable());
        $entreprise->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($entreprise);

        //MONNAIES
        $monnaieUSD = null;
        foreach ($tabCodesMonnaies as $codeMonnaie) {
            //Pour chaque element du tableau
            $monnaie = new Monnaie();
            if ($codeMonnaie == "CDF") {
                $monnaie->setNom("Franc Congolais");
                $monnaie->setTauxusd(1);
                $monnaie->setIslocale(true);
            } else {
                $monnaie->setNom("Dollars Américains");
                $monnaie->setTauxusd(2050);
                $monnaie->setIslocale(false);
                $monnaieUSD = $monnaie;
            }
            $monnaie->setCode($codeMonnaie);
            $monnaie->setEntreprise($entreprise);
            $monnaie->setCreatedAt(new \DateTimeImmutable());
            $monnaie->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($monnaie);
        }

        //TAXES
        foreach ($tabNomsTaxes as $nomTaxes) {
            //Pour chaque element du tableau
            $taxe = new Taxe();
            $taxe->setNom($nomTaxes);
            if ($nomTaxes == "TVA") {
                $taxe->setDescription("Taxe sur la Valeur Ajoutée");
                $taxe->setTaux(0.16);
                $taxe->setPayableparcourtier(false);
                $taxe->setOrganisation("DGI - Direction Générale des Impôts");
            } else {
                $taxe->setDescription("Frais de surveillance");
                $taxe->setTaux(0.02);
                $taxe->setPayableparcourtier(true);
                $taxe->setOrganisation("ARCA - Autorité de Régulation des Assurances");
            }
            $taxe->setEntreprise($entreprise);
            $taxe->setCreatedAt(new \DateTimeImmutable());
            $taxe->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($taxe);
        }


        //PARTENAIRES
        foreach ($tabNomsPartenaires as $nomPartenaire) {
            $partenaire = new Partenaire();
            $partenaire->setNom($nomPartenaire);
            $partenaire->setAdresse($faker->address());
            $partenaire->setEmail($faker->email());
            $partenaire->setSiteweb($faker->url());
            $partenaire->setRccm("RCCM" . $faker->randomNumber(5, true));
            $partenaire->setIdnat("IDNAT" . $faker->randomNumber(5, true));
            $partenaire->setNumimpot("IMP" . $faker->randomNumber(5, true));
            $partenaire->setPart(50);
            $partenaire->setEntreprise($entreprise);
            $partenaire->setCreatedAt(new \DateTimeImmutable());
            $partenaire->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($partenaire);
        }

        //ASSUREURS
        foreach ($tabNomsAssureurs as $nomAssureur) {
            $assureur = new Assureur();
            $assureur->setNom($nomAssureur);
            $assureur->setAdresse($faker->address());
            $assureur->setTelephone($faker->phoneNumber());
            $assureur->setEmail($faker->email());
            $assureur->setSiteweb($faker->url());
            $assureur->setRccm("RCCM" . $faker->randomNumber(5, true));
            $assureur->setIdnat("IDNAT" . $faker->randomNumber(5, true));
            $assureur->setLicence("ARCA" . $faker->randomNumber(3, true));
            $assureur->setNumimpot("IMP" . $faker->randomNumber(5, true));
            $assureur->setIsreassureur(false);
            $assureur->setEntreprise($entreprise);
            $assureur->setCreatedAt(new \DateTimeImmutable());
            $assureur->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($assureur);
        }

        //Autres assureurs
        for ($i = 0; $i < 50; $i++) {
            $assureur = new Assureur();
            $assureur->setNom($faker->company() . " Insurance LTD");
            $assureur->setAdresse($faker->address());
            $assureur->setTelephone($faker->phoneNumber());
            $assureur->setEmail($faker->email());
            $assureur->setSiteweb($faker->url());
            $assureur->setRccm("RCCM" . $faker->randomNumber(5, true));
            $assureur->setIdnat("IDNAT" . $faker->randomNumber(5, true));
            $assureur->setLicence("ARCA" . $faker->randomNumber(3, true));
            $assureur->setNumimpot("IMP" . $faker->randomNumber(5, true));
            $assureur->setIsreassureur(true);
            $assureur->setEntreprise($entreprise);
            $assureur->setCreatedAt(new \DateTimeImmutable());
            $assureur->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($assureur);
        }

        //PRODUIT
        $compteur = 0;
        foreach ($tabNomsProduits as $nomProduit) {
            $produit = new Produit();
            $produit->setNom($nomProduit);
            $produit->setCode("PRD" . $faker->randomNumber(5, true));
            $produit->setDescription($faker->sentence(5));
            if ($compteur % 2) {
                $produit->setIsobligatoire(true);
                $produit->setTauxarca(0.10);
            } else {
                $produit->setIsobligatoire(false);
                $produit->setTauxarca(0.15);
            }
            $produit->setIsabonnement(false);
            $produit->setCategorie(0);
            $produit->setEntreprise($entreprise);
            $produit->setCreatedAt(new \DateTimeImmutable());
            $produit->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($produit);
            $compteur++;
        }

        //CLIENTS
        $compteur = 0;
        for ($i = 0; $i < 100; $i++) {
            $client = new Client();
            $client->setAdresse($faker->address());
            $client->setTelephone($faker->phoneNumber());
            $client->setEmail($faker->email());
            $client->setSiteweb($faker->url());
            if ($compteur < 30) {
                $client->setNom($faker->name());
                $client->setIspersonnemorale(false);
                $client->setRccm("");
                $client->setIdnat("");
                $client->setNumipot("");
                $client->setSecteur(0);
            } else {
                $client->setNom($faker->company());
                $client->setIspersonnemorale(true);
                $client->setRccm("RCCM" . $faker->randomNumber(5, true));
                $client->setIdnat("IDNAT" . $faker->randomNumber(5, true));
                $client->setNumipot("IMP" . $faker->randomNumber(5, true));
                $client->setSecteur(2);
            }
            $client->setEntreprise($entreprise);
            $client->setCreatedAt(new \DateTimeImmutable());
            $client->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($client);
            $compteur++;

            //Chaque client a des contacts
            for ($j = 0; $j < 3; $j++) {
                $contact = new Contact();
                $contact->setNom($faker->name());
                $contact->setPoste($faker->jobTitle());
                $contact->setTelephone($faker->phoneNumber());
                $contact->setEmail($faker->email());
                $contact->setClient($client);
                $contact->setEntreprise($entreprise);
                $contact->setCreatedAt(new \DateTimeImmutable());
                $contact->setUpdatedAt(new \DateTimeImmutable());

                $manager->persist($contact);
            }
        }


        //AUTOMOBILES
        foreach ($tabMarquesAutomobiles as $marqueAuto) {
            for ($a = 0; $a < 5; $a++) {
                $auto = new Automobile();
                $auto->setAnnee($faker->numberBetween(2001, 2022));
                $auto->setModel($faker->numerify('MODEL-####'));
                $auto->setMarque($marqueAuto);
                $auto->setPuissance($faker->numberBetween(8, 20) . "CV");
                $auto->setValeur($faker->numberBetween(1000, 25000));
                $auto->setMonnaie($monnaieUSD);
                $auto->setNbsieges($faker->numberBetween(4, 8));
                $auto->setNature(1);
                $auto->setUtilite(1);
                $auto->setPlaque($faker->randomNumber(4, true) . "BG/0" . $a);
                $auto->setChassis("XCD4" . $faker->randomNumber(5, true));
                $auto->setCreatedAt(new \DateTimeImmutable());
                $auto->setUpdatedAt(new \DateTimeImmutable());
                $auto->setEntreprise($entreprise);
                $manager->persist($auto);
            }
        }




        //GESTION SINISTRE
        //VICTIME
        for ($a = 0; $a < 15; $a++) {
            $victime = new Victime();
            $victime->setNom($faker->name());
            $victime->setAdresse($faker->address());
            $victime->setTelephone($faker->phoneNumber());
            $victime->setEmail($faker->email());
            $victime->setEntreprise($entreprise);
            $victime->setCreatedAt(new \DateTimeImmutable());
            $victime->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($victime);
        }

        //EXPERT
        for ($a = 0; $a < 5; $a++) {
            $expert = new Expert();
            $expert->setNom($faker->name());
            $expert->setAdresse($faker->address());
            $expert->setTelephone($faker->phoneNumber());
            $expert->setDescription("Blabla blablablablabla Blabla blablablablabla Blabla blablablablabla");
            $expert->setSiteweb($faker->url());
            $expert->setEmail($faker->email());
            $expert->setEntreprise($entreprise);
            $expert->setCreatedAt(new \DateTimeImmutable());
            $expert->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($expert);
        }

        //ETAPE
        for ($a = 0; $a <4 ; $a++) {
            $etapeSinistre = new EtapeSinistre();
            $etapeSinistre->setNom("Etape " . $a);
            $etapeSinistre->setIndice($a);
            $etapeSinistre->setDescription("Blabla blablablablabla Blabla blablablablabla Blabla blablablablabla");
            $etapeSinistre->setEntreprise($entreprise);
            $etapeSinistre->setCreatedAt(new \DateTimeImmutable());
            $etapeSinistre->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($etapeSinistre);
        }


        //COMMENTAIRE
        for ($a = 0; $a < 10; $a++) {
            $comment = new CommentaireSinistre();
            $comment->setMessage("Blabla blablablablabla Blabla blablablablabla Blabla blablablablabla");
            $comment->setEntreprise($entreprise);
            $comment->setUtilisateur($user_admin);
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($comment);
        }

        //DOC - CATEGORIE
        $doc_categorie_police = new DocCategorie();
        $doc_categorie_police->setNom("Police d'assurance");
        $doc_categorie_police->setUtilisateur($user_admin);
        $doc_categorie_police->setEntreprise($entreprise);
        $doc_categorie_police->setCreatedAt(new \DateTimeImmutable());
        $doc_categorie_police->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_categorie_police);

        $doc_categorie_form = new DocCategorie();
        $doc_categorie_form->setNom("Formulaire de proposition");
        $doc_categorie_form->setUtilisateur($user_admin);
        $doc_categorie_form->setEntreprise($entreprise);
        $doc_categorie_form->setCreatedAt(new \DateTimeImmutable());
        $doc_categorie_form->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_categorie_form);

        $doc_categorie_bor = new DocCategorie();
        $doc_categorie_bor->setNom("Mandat de courtage");
        $doc_categorie_bor->setUtilisateur($user_admin);
        $doc_categorie_bor->setEntreprise($entreprise);
        $doc_categorie_bor->setCreatedAt(new \DateTimeImmutable());
        $doc_categorie_bor->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_categorie_bor);

        //DOC CLASSEUR
        $doc_classeur_andy = new DocClasseur();
        $doc_classeur_andy->setNom("PRODUCTION - ANDY SAMBI");
        $doc_classeur_andy->setUtilisateur($user_admin);
        $doc_classeur_andy->setEntreprise($entreprise);
        $doc_classeur_andy->setCreatedAt(new \DateTimeImmutable());
        $doc_classeur_andy->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_classeur_andy);

        $doc_classeur_michee = new DocClasseur();
        $doc_classeur_michee->setNom("PRODUCTION - MICHEE MURUND");
        $doc_classeur_michee->setUtilisateur($user_admin);
        $doc_classeur_michee->setEntreprise($entreprise);
        $doc_classeur_michee->setCreatedAt(new \DateTimeImmutable());
        $doc_classeur_michee->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_classeur_michee);

        $doc_classeur_syntyche = new DocClasseur();
        $doc_classeur_syntyche->setNom("PRODUCTION - SYNTYCHE MWEMA");
        $doc_classeur_syntyche->setUtilisateur($user_admin);
        $doc_classeur_syntyche->setEntreprise($entreprise);
        $doc_classeur_syntyche->setCreatedAt(new \DateTimeImmutable());
        $doc_classeur_syntyche->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_classeur_syntyche);

        //DOC PIECES
        $doc_piece_a = new DocPiece();
        $doc_piece_a->setNom("Police 1245787878/2023-10001 - Documentation");
        $doc_piece_a->setDescription("Bablablablabla blablabla Police 1245787878/2023-10001 - Documentation");
        $doc_piece_a->addCategorie($doc_categorie_police);
        $doc_piece_a->addClasseur($doc_classeur_andy);
        $doc_piece_a->setUtilisateur($user_admin);
        $doc_piece_a->setEntreprise($entreprise);
        $doc_piece_a->setCreatedAt(new \DateTimeImmutable());
        $doc_piece_a->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_piece_a);


        $doc_piece_a = new DocPiece();
        $doc_piece_a->setNom("Police 1245787878/2023-10012 - Documentation");
        $doc_piece_a->setDescription("Bablablablabla blablabla Police 1245787878/2023-10001 - Documentation");
        $doc_piece_a->addCategorie($doc_categorie_police);
        $doc_piece_a->addClasseur($doc_classeur_michee);
        $doc_piece_a->setUtilisateur($user_admin);
        $doc_piece_a->setEntreprise($entreprise);
        $doc_piece_a->setCreatedAt(new \DateTimeImmutable());
        $doc_piece_a->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_piece_a);


        $doc_piece_a = new DocPiece();
        $doc_piece_a->setNom("Police 1245787878/2023-10145 - Documentation");
        $doc_piece_a->setDescription("Bablablablabla blablabla Police 1245787878/2023-10001 - Documentation");
        $doc_piece_a->addCategorie($doc_categorie_police);
        $doc_piece_a->addClasseur($doc_classeur_syntyche);
        $doc_piece_a->setUtilisateur($user_admin);
        $doc_piece_a->setEntreprise($entreprise);
        $doc_piece_a->setCreatedAt(new \DateTimeImmutable());
        $doc_piece_a->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($doc_piece_a);


        //ETAPE CRM
        $etape_crm_a = new EtapeCrm();
        $etape_crm_a->setNom("Prospection");
        $etape_crm_a->setUtilisateur($user_admin);
        $etape_crm_a->setEntreprise($entreprise);
        $etape_crm_a->setCreatedAt(new \DateTimeImmutable());
        $etape_crm_a->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($etape_crm_a);

        $etape_crm_a = new EtapeCrm();
        $etape_crm_a->setNom("RFQ - Demande de cotation");
        $etape_crm_a->setUtilisateur($user_admin);
        $etape_crm_a->setEntreprise($entreprise);
        $etape_crm_a->setCreatedAt(new \DateTimeImmutable());
        $etape_crm_a->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($etape_crm_a);

        $etape_crm_a = new EtapeCrm();
        $etape_crm_a->setNom("Placement du risque");
        $etape_crm_a->setUtilisateur($user_admin);
        $etape_crm_a->setEntreprise($entreprise);
        $etape_crm_a->setCreatedAt(new \DateTimeImmutable());
        $etape_crm_a->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($etape_crm_a);

        //ACTION
        $action = new ActionCRM();
        $action->setMission("Organiser un RDV pour discuter des risques potentiels.");
        $action->setObjectif("Comprendre les risques assurables du client et faire des propositions.");
        $action->setStartedAt(new \DateTimeImmutable());
        $action->setEndedAt(new \DateTimeImmutable());
        $action->setClos(true);
        $action->addAttributedTo($user_admin);
        $action->setUtilisateur($user_admin);
        $action->setEntreprise($entreprise);
        $action->setCreatedAt(new \DateTimeImmutable());
        $action->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($action);

        //Feedback
        $feedback = new FeedbackCRM();
        $feedback->setMessage("Bjr à tous. Action axécutée avec succès.");
        $feedback->setAction($action);
        $feedback->setEntreprise($entreprise);
        $feedback->setUtilisateur($user_admin);
        $feedback->setCreatedAt(new \DateTimeImmutable());
        $feedback->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($feedback);

        //PISTE
        $piste = new Piste();
        $piste->setNom("BGFIBank RDC SA - BBB");
        $piste->setObjectif("Gagner l'affaire BBB - BGFIbank RDC SA");
        $piste->setMontant(450000);
        $piste->setExpiredAt(new \DateTimeImmutable());
        $piste->setEtape($etape_crm_a);
        $piste->addAction($action);
        $piste->setUtilisateur($user_admin);
        $piste->setEntreprise($entreprise);
        $piste->setCreatedAt(new \DateTimeImmutable());
        $piste->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($piste);

        $manager->flush();
    }
}
