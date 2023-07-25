<?php

namespace App\Service;

use App\Controller\Admin\MonnaieCrudController;
use App\Entity\Entreprise;
use App\Entity\Monnaie;
use App\Entity\Taxe;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use NumberFormatter;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceTaxes
{
    private ?Utilisateur $utilisateur = null;
    private ?Entreprise $entreprise = null;
    private $taxes = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
        //Chargement de l'utilisateur et de l'entreprise
        $this->utilisateur = $this->serviceEntreprise->getUtilisateur();
        $this->entreprise = $this->serviceEntreprise->getEntreprise();

        //Chargement des monnaies
        $this->chargerTaxes();
    }

    private function chargerTaxes()
    {
        $this->taxes = $this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->entreprise]
        );
    }

    private function getTaxe(bool $payableParCourtier): Taxe
    {
        foreach ($this->taxes as $taxe) {
            //dd($fonction);
            if($taxe->isPayableparcourtier() == $payableParCourtier){
                return $taxe;
            }
        }
        return null;
    }

    public function getTaxe_Courtier()
    {
        return $this->getTaxe(true);
    }

    public function getTaxe_Assureur()
    {
        return $this->getTaxe(false);
    }

    public function getNomTaxeCourtier()
    {
        return $this->getTaxe_Courtier()->getNom();
    }

    public function getNomTaxeAssureur()
    {
        return $this->getTaxe_Assureur()->getNom();
    }
}
