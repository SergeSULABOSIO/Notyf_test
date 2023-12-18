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

    private ?array $criteresNonMappes = [];

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

    public function definirFiltreNonMappe(array $criteresNonMappes, Filters $filters): Filters
    {
        $this->criteresNonMappes = $criteresNonMappes;
        foreach ($this->criteresNonMappes as $attribut => $parametres) {
            if (is_bool($parametres["defaultValue"])) {
                $filters->add($this->getFiltreBooleanNonMappe($attribut, $parametres["label"], $parametres["options"]));
            } else {
                $filters->add($this->getFiltreEntiteNonMappe($attribut, $parametres["label"], $parametres["multipleChoices"], $parametres["class"]));
            }
        }


        return $filters
            //Par validité //$this->
            // ->add($this->getFiltreBooleanNonMappe("validated", "Validée?", [
            //     "Oui" => true,
            //     "Non" => false,
            // ]))
            // ->add($this->getFiltreEntiteNonMappe("police", "Police", true, Police::class))
            // ->add($this->getFiltreEntiteNonMappe("client", "Client", true, Client::class))
            // ->add($this->getFiltreEntiteNonMappe("produit", "Produit", true, Produit::class))
            // ->add($this->getFiltreEntiteNonMappe("assureur", "Assureur", true, Assureur::class))
            // ->add($this->getFiltreEntiteNonMappe("partenaire", "Partenaire", true, Partenaire::class))
            //
        ;
    }

    public function retirerCritere(?string $attributARetirer, $valeurParDefaut, SearchDto $searchDto)
    {
        $criterRetire = $valeurParDefaut; //Par defaut on ne filtre qu'avec le critère TRUE
        if (isset($searchDto->getAppliedFilters()[$attributARetirer])) {
            $appliedFilters = $searchDto->getAppliedFilters();
            if (isset($appliedFilters[$attributARetirer]['value'])) {
                $criterRetire = $appliedFilters[$attributARetirer]['value'];
            }
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

    public function getOperateur($critere): string
    {
        //dd($critere);
        $operateur = "=";
        if ($critere != null) {
            if (is_array($critere)) {
                $operateur = "IN";
                //dd($critere);
            } else {
                $operateur = "=";
            }
        }
        return $operateur;
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
    public function appliquerCriteresAttributsNonMappes(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters, callable $ecouteur): QueryBuilder
    {
        //On retire les critères non mappés
        //dd($this->criteresNonMappes);
        foreach ($this->criteresNonMappes as $attribut => $parametres) {
            $data = $this->retirerCritere($attribut, $parametres["defaultValue"], $searchDto);
            $searchDto = $data['searchDto'];
            $this->criteresNonMappes[$attribut]["userValues"] = $data['criterRetire'];
        }
        //dd($this->criteresNonMappes);
        //validee
        // $data = $this->retirerCritere('validated', true, $searchDto);
        // $searchDto = $data['searchDto'];
        // $validee = $data['criterRetire'];
        //police
        // $data = $this->retirerCritere('police', [], $searchDto);
        // $searchDto = $data['searchDto'];
        // $police = $data['criterRetire'];
        //dd($police);
        // //client
        // $data = $this->retirerCritere('client', [], $searchDto);
        // $searchDto = $data['searchDto'];
        // $client = $data['criterRetire'];
        // //partenaire
        // $data = $this->retirerCritere('partenaire', [], $searchDto);
        // $searchDto = $data['searchDto'];
        // $partenaire = $data['criterRetire'];
        // //produit
        // $data = $this->retirerCritere('produit', [], $searchDto);
        // $searchDto = $data['searchDto'];
        // $produit = $data['criterRetire'];
        // //assureur
        // $data = $this->retirerCritere('assureur', [], $searchDto);
        // $searchDto = $data['searchDto'];
        // $assureur = $data['criterRetire'];
        //dd($filters);
        //$defaultQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        $defaultQueryBuilder = $ecouteur($searchDto, $entityDto, $fields, $filters);

        //Exécution des requêtes de jointures
        $indiceRequete = 1;
        //dd($this->criteresNonMappes);
        foreach ($this->criteresNonMappes as $attribut => $parametres) {
            if ($this->canExcuterJointure($parametres["userValues"])) {
                $nomRequete = 'requete' . $indiceRequete;
                $operateur = $this->getOperateur($parametres["userValues"]);
                $defaultQueryBuilder
                    ->join('entity.' . $parametres["joiningEntity"], $nomRequete)
                    ->andWhere($nomRequete . '.' . $attribut . ' ' . $operateur . ' (:userValues'.$nomRequete.')') //si validee n'est pas un tableau
                    ->setParameter('userValues'.$nomRequete, $parametres["userValues"]);
                $indiceRequete = $indiceRequete + 1;
            }
        }
        //dd($this->criteresNonMappes);
        //dd($defaultQueryBuilder);
        // //critere Validee
        // if ($this->canExcuterJointure($validee)) {
        //     $defaultQueryBuilder->join('entity.cotation', 'requete1')
        //         ->andWhere('requete1.validated = (:validee)') //si validee n'est pas un tableau
        //         ->setParameter('validee', $validee);
        // }
        //critere Police
        // if ($this->canExcuterJointure($police)) {
        //     $defaultQueryBuilder->join('entity.cotation', 'requete2')
        //         ->andWhere('requete2.police IN (:police)') //si validee n'est pas un tableau
        //         ->setParameter('police', $police);
        // }
        // //critere Client
        // if ($this->canExcuterJointure($client)) {
        //     $defaultQueryBuilder->join('entity.cotation', 'requete3')
        //         ->andWhere('requete3.client IN (:client)') //si validee n'est pas un tableau
        //         ->setParameter('client', $client);
        // }
        //ici
        return $defaultQueryBuilder;
    }
}
