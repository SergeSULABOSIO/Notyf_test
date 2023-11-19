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
    public const TAXE_COURTIER = 0;
    public const TAXE_ASSUREUR = 1;

    private ?Entreprise $entreprise = null;
    private $taxes = [];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private Security $security
    ) {
        //Chargement de l'utilisateur et de l'entreprise
        $this->entreprise = $this->serviceEntreprise->getEntreprise();

        //Chargement des taxes
        $this->taxes = $this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->entreprise]
        );
    }


    public function getTaxe(bool $payableParCourtier)
    {
        foreach ($this->taxes as $taxe) {
            //dd($fonction);
            if ($taxe->isPayableparcourtier() == $payableParCourtier) {
                return $taxe;
            }
        }
        return null;
    }

    public function getNomTaxeCourtier()
    {
        /** @var Taxe */
        $taxe = $this->getTaxe(true);
        $txt = $taxe != null ? strtolower($taxe."") : "Tx Courtier";
        return $txt;
    }

    public function getNomTaxeAssureur()
    {
        /** @var Taxe */
        $taxe = $this->getTaxe(false);
        $txt = $taxe != null ? strtolower($taxe."") : "Tx Assureur";
        return $txt;
    }
}
