<?php

namespace App\Service;

use App\Controller\Admin\MonnaieCrudController;
use App\Entity\Entreprise;
use App\Entity\Monnaie;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use NumberFormatter;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\SecurityBundle\Security;


class ServiceMonnaie
{
    private ?Utilisateur $utilisateur = null;
    private ?Entreprise $entreprise = null;
    private $monnaies = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
        //Chargement de l'utilisateur et de l'entreprise
        $this->utilisateur = $this->serviceEntreprise->getUtilisateur();
        $this->entreprise = $this->serviceEntreprise->getEntreprise();

        //Chargement des monnaies
        $this->chargerMonnaies();
    }

    private function chargerMonnaies()
    {
        $this->monnaies = $this->entityManager->getRepository(Monnaie::class)->findBy(
            ['entreprise' => $this->entreprise]
        );
    }

    private function getMonnaie($fonction)
    {
        foreach ($this->monnaies as $monnaie) {
            //dd($fonction);
            if($monnaie->getFonction() == $fonction){
                return $monnaie;
            }
        }
        return null;
    }

    public function getMonnaie_Affichage()
    {
        $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if($monnaie == null){
            $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_AFFICHAGE_UNIQUEMENT]);
        }
        return $monnaie;
    }

    public function getMonnaie_Saisie()
    {
        $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_ET_AFFICHAGE]);
        if($monnaie == null){
            $monnaie = $this->getMonnaie(MonnaieCrudController::TAB_MONNAIE_FONCTIONS[MonnaieCrudController::FONCTION_SAISIE_UNIQUEMENT]);
        }
        return $monnaie;
    }

    public function getCodeSaisie()
    {
        return $this->getMonnaie_Saisie()->getCode();
    }

    public function getCodeAffichage()
    {
        return $this->getMonnaie_Affichage()->getCode();
    }

    public function mustConvertCurrency(): bool
    {
        if($this->getMonnaie_Affichage() == $this->getMonnaie_Saisie()){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Cette fonction retourne le montant passé en paramètre en monnaie d'affichage défini dans cette application.
     *
     * @param [type] $montant
     * @return string
     */
    public function getMonantEnMonnaieAffichage($montant):string
    {
        //Monnaie de saisie
        $monnaieSaisie = $this->getMonnaie_Saisie();
        $tauxUSDSaisie = $monnaieSaisie->getTauxusd() / 100;
        //Montant saisie en USD
        $mntInputInUSD = $montant * $tauxUSDSaisie;
        //Monnaie d'affichage
        $monnaieAffichage = $this->getMonnaie_Affichage();
        $tauxUSDAffichage = $monnaieAffichage->getTauxusd() / 100;
        //Montant saisie en Monnaie d'affichage
        $mntOutput = ($mntInputInUSD / $tauxUSDAffichage) / 100;
        //Application du format monnétaire en anglais
        $fmt = numfmt_create('en_US', NumberFormatter::CURRENCY);
        return numfmt_format_currency($fmt, $mntOutput, $monnaieAffichage->getCode());
    }

}
