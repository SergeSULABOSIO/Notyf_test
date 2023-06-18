<?php

namespace App\Service;

use App\Controller\Admin\PreferenceCrudController;
use App\Entity\Entreprise;
use App\Entity\Preference;
use App\Entity\Utilisateur;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;

class ServicePreferences
{
    //public const PAREMETRE_UTILISATEUR = 25;
    //public const PAREMETRE_ENTREPRISE = 26;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) 
    {

    }

    public function appliquerPreferenceApparence(Dashboard $dashboard, Utilisateur $utilisateur, Entreprise $entreprise)
    {
        $preferences = $this->entityManager->getRepository(Preference::class)->findBy(
            [
                'entreprise' => $entreprise,
                'utilisateur' => $utilisateur,
            ]
        );
        if ($preferences[0]->getApparence() == 0) {
            $dashboard->disableDarkMode();
        }
    }

    public function creerPreference($utilisateur, $entreprise)
    {
        $preference = new Preference();
        $preference->setApparence(0);
        $preference->setUtilisateur($utilisateur);
        $preference->setEntreprise($entreprise);
        $preference->setCreatedAt(new DateTimeImmutable());
        $preference->setUpdatedAt(new DateTimeImmutable());
        //CRM
        $preference->setCrmTaille(100);
        $preference->setCrmMissions([0,1]);
        $preference->setCrmFeedbacks([0,1]);
        $preference->setCrmCotations([0,1]);
        $preference->setCrmEtapes([0,1]);
        $preference->setCrmPistes([0,1]);
        //PRO
        $preference->setProTaille(100);
        $preference->setProAssureurs([0,1]);
        $preference->setProAutomobiles([0,1]);
        $preference->setProContacts([0,1]);
        $preference->setProClients([0,1]);
        $preference->setProPartenaires([0,1]);
        $preference->setProPolices([0,1]);
        $preference->setProProduits([0,1]);
        //FIN
        $preference->setFinTaille(100);
        $preference->setFinTaxes([0,1]);
        $preference->setFinMonnaies([0,1]);
        $preference->setFinCommissionsPayees([0,1]);
        $preference->setFinRetrocommissionsPayees([0,1]);
        $preference->setFinTaxesPayees([0,1]);
        //SIN
        $preference->setSinTaille(100);
        $preference->setSinCommentaires([0,1]);
        $preference->setSinEtapes([0,1]);
        $preference->setSinSinistres([0,1]);
        $preference->setSinVictimes([0,1]);
        //BIB
        $preference->setBibTaille(100);
        $preference->setBibCategories([0,1]);
        $preference->setBibClasseurs([0,1]);
        $preference->setBibPieces([0,1]);
        //PAR
        $preference->setParTaille(100);
        $preference->setParUtilisateurs([0,1]);

        //persistance
        $this->entityManager->persist($preference);
        $this->entityManager->flush();
    }
}
