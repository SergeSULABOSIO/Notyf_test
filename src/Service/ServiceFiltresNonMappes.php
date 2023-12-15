<?php

namespace App\Service;

use App\Entity\Taxe;
use App\Entity\Client;
use App\Entity\Police;
use App\Entity\Facture;
use App\Entity\Produit;
use App\Entity\Assureur;
use App\Entity\Paiement;
use App\Entity\Sinistre;
use App\Entity\Partenaire;
use App\Entity\ElementFacture;
use Doctrine\ORM\QueryBuilder;
use App\Entity\CalculableEntity;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\FactureCrudController;
use App\Entity\EcouteurServiceFiltresNonMappes;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;


class ServiceFiltresNonMappes
{

    public function __construct(
        private ServiceEntreprise $serviceEntreprise,
    ) {
    }

    public function getFiltreEntiteNonMappe(?string $nomAttribut, ?string $label, ?bool $choixMultiple, $classe): FilterInterface
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        return EntityFilter::new($nomAttribut, $label)
            ->setFormTypeOption('value_type_options.class', $classe)
            ->setFormTypeOption(
                'value_type_options.query_builder',
                static fn (EntityRepository $repository) => $repository
                    ->createQueryBuilder($nomAttribut)
                    //On ne parcours que les enregistrement de cet entreprise
                    ->where($nomAttribut . '.entreprise = :ese')
                    ->setParameter('ese', $connected_entreprise)
                    ->orderBy($nomAttribut . '.id', 'ASC')
            )
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('value_type_options.multiple', $choixMultiple);
    }


    public function getFiltreBooleanNonMappe(?string $nomAttribut, ?string $label, ?array $choices): FilterInterface
    {
        return ChoiceFilter::new($nomAttribut, $label)
            ->setChoices($choices)
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('value_type_options.multiple', false);
    }

    public function definirFiltreNonMappe(Filters $filters): Filters
    {
        return $filters
            //Par validité //$this->
            ->add($this->getFiltreBooleanNonMappe("validee", "Validée?", [
                "Oui" => true,
                "Non" => false,
            ]))
            ->add($this->getFiltreEntiteNonMappe("police", "Police", true, Police::class))
            ->add($this->getFiltreEntiteNonMappe("client", "Client", true, Client::class))
            ->add($this->getFiltreEntiteNonMappe("produit", "Produit", true, Produit::class))
            ->add($this->getFiltreEntiteNonMappe("assureur", "Assureur", true, Assureur::class))
            ->add($this->getFiltreEntiteNonMappe("partenaire", "Partenaire", true, Partenaire::class));
    }

    public function retirerCritere(?string $attributARetirer, $valeurParDefaut, SearchDto $searchDto)
    {
        $criterRetire = $valeurParDefaut; //Par defaut on ne filtre qu'avec le critère TRUE
        if (isset($searchDto->getAppliedFilters()[$attributARetirer])) {
            $appliedFilters = $searchDto->getAppliedFilters();
            $criterRetire = $appliedFilters[$attributARetirer]['value'];
            unset($appliedFilters[$attributARetirer]);

            $searchDto = new SearchDto(
                $searchDto->getRequest(),
                $searchDto->getSearchableProperties(),
                $searchDto->getQuery(),
                [],
                $searchDto->getSort(),
                $appliedFilters
            );
        }
        return [
            'searchDto' => $searchDto,
            'criterRetire' => $criterRetire
        ];
        //return $criterRetire;
    }

    public function canExcuterJointure($critere): bool
    {
        $reponse = false;
        if (is_array($critere)) {
            if (count($critere)) {
                $reponse = true;
            }
        } else {
            if (isset($critere)) {
                $reponse = true;
            }
        }
        return $reponse;
    }

    /**
     * Cette fonction permet de définir comme l'un des critères un champ non mappé dans l'entité.
     * En réalité, on lancera une autre requête SQL séparée et fera la jointure.
     *
     * @param SearchDto $searchDto
     * @param EntityDto $entityDto
     * @param FieldCollection $fields
     * @param FilterCollection $filters
     * @return QueryBuilder
     */
    public function appliquerCriteresAttributsNonMappes(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters, ?EcouteurServiceFiltresNonMappes $ecouteur): QueryBuilder
    {
        //validee
        $data = $this->retirerCritere('validee', true, $searchDto);
        $searchDto = $data['searchDto'];
        $validee = $data['criterRetire'];
        //police
        $data = $this->retirerCritere('police', [], $searchDto);
        $searchDto = $data['searchDto'];
        $police = $data['criterRetire'];
        //client
        $data = $this->retirerCritere('client', [], $searchDto);
        $searchDto = $data['searchDto'];
        $client = $data['criterRetire'];
        //partenaire
        $data = $this->retirerCritere('partenaire', [], $searchDto);
        $searchDto = $data['searchDto'];
        $partenaire = $data['criterRetire'];
        //produit
        $data = $this->retirerCritere('produit', [], $searchDto);
        $searchDto = $data['searchDto'];
        $produit = $data['criterRetire'];
        //assureur
        $data = $this->retirerCritere('assureur', [], $searchDto);
        $searchDto = $data['searchDto'];
        $assureur = $data['criterRetire'];


        //$defaultQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $defaultQueryBuilder = $ecouteur->genererQueryBuilder($searchDto, $entityDto, $fields, $filters);
        //Exécution des requêtes de jointures
        //critere Validee
        if ($this->canExcuterJointure($validee)) {
            $defaultQueryBuilder->join('entity.cotation', 'requete1')
                ->andWhere('requete1.validated = (:validee)') //si validee n'est pas un tableau
                ->setParameter('validee', $validee);
        }
        //critere Police
        if ($this->canExcuterJointure($police)) {
            $defaultQueryBuilder->join('entity.cotation', 'requete2')
                ->andWhere('requete2.police IN (:police)') //si validee n'est pas un tableau
                ->setParameter('police', $police);
        }
        //critere Client
        if ($this->canExcuterJointure($client)) {
            $defaultQueryBuilder->join('entity.cotation', 'requete3')
                ->andWhere('requete3.client IN (:client)') //si validee n'est pas un tableau
                ->setParameter('client', $client);
        }
        //ici
        return $defaultQueryBuilder;
    }
}
