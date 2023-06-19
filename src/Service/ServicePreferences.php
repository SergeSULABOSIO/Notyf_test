<?php

namespace App\Service;

use App\Entity\Taxe;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Expert;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Contact;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\Victime;
use App\Entity\Assureur;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\EtapeCrm;
use App\Entity\Sinistre;
use App\Entity\ActionCRM;
use App\Entity\Automobile;
use App\Entity\Entreprise;
use App\Entity\Partenaire;
use App\Entity\Preference;
use App\Entity\DocClasseur;
use App\Entity\FeedbackCRM;
use App\Entity\Utilisateur;
use App\Entity\DocCategorie;
use App\Entity\PaiementTaxe;
use App\Entity\EtapeSinistre;
use App\Entity\PaiementCommission;
use App\Entity\PaiementPartenaire;
use Doctrine\ORM\EntityRepository;
use App\Entity\CommentaireSinistre;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ServicePreferences
{
    private $preferences;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise
    ) {
    }

    public function chargerPreference(Utilisateur $utilisateur, Entreprise $entreprise): Preference
    {
        $preferences = $this->entityManager->getRepository(Preference::class)->findBy(
            [
                'entreprise' => $entreprise,
                'utilisateur' => $utilisateur,
            ]
        );
        return $preferences[0];
    }

    public function appliquerPreferenceApparence(Dashboard $dashboard, Utilisateur $utilisateur, Entreprise $entreprise)
    {
        $preference = $this->chargerPreference($utilisateur, $entreprise);
        if ($preference->getApparence() == 0) {
            $dashboard->disableDarkMode();
        }
    }

    public function appliquerPreferenceTaille($instance, Crud $crud)
    {
        $preference = $this->chargerPreference($this->serviceEntreprise->getUtilisateur(), $this->serviceEntreprise->getEntreprise());
        if ($instance instanceof EtapeCrm) {
            $taille = 100;
            if ($preference->getCrmTaille() != 0) {
                $taille = $preference->getCrmTaille();
            }
            $crud->setPaginatorPageSize($taille);
        }
    }

    public function canShow(array $tab, $indice_attribut)
    {
        foreach ($tab as $valeur) {
            if ($valeur == $indice_attribut) {
                return true;
            }
        }
        return false;
    }

    public function definirAttributsPages($objetInstance, Preference $preference, $tabAttributs)
    {
        //GROUPE CRM
        if ($objetInstance instanceof ActionCRM) {
        }
        if ($objetInstance instanceof FeedbackCRM) {
        }
        if ($objetInstance instanceof Cotation) {
        }
        if ($objetInstance instanceof EtapeCrm) {
            $tabAttributs = $this->setCRM_Fields_Etapes($preference, $tabAttributs);
        }
        if ($objetInstance instanceof Piste) {
            $tabAttributs = $this->setCRM_Fields_Pistes($preference, $tabAttributs);
        }
        //GROUPE PRODUCTION
        if ($objetInstance instanceof Assureur) {
        }
        if ($objetInstance instanceof Automobile) {
        }
        if ($objetInstance instanceof Contact) {
        }
        if ($objetInstance instanceof Client) {
        }
        if ($objetInstance instanceof Partenaire) {
        }
        if ($objetInstance instanceof Police) {
        }
        if ($objetInstance instanceof Produit) {
        }
        //GROUPE FINANCES
        if ($objetInstance instanceof Taxe) {
        }
        if ($objetInstance instanceof Monnaie) {
        }
        if ($objetInstance instanceof PaiementCommission) {
        }
        if ($objetInstance instanceof PaiementPartenaire) {
        }
        if ($objetInstance instanceof PaiementTaxe) {
        }
        //GROUPE SINISTRE
        if ($objetInstance instanceof CommentaireSinistre) {
        }
        if ($objetInstance instanceof EtapeSinistre) {
        }
        if ($objetInstance instanceof Expert) {
        }
        if ($objetInstance instanceof Sinistre) {
        }
        if ($objetInstance instanceof Victime) {
        }
        //GROUPE BIBLIOTHEQUE
        if ($objetInstance instanceof DocCategorie) {
        }
        if ($objetInstance instanceof DocClasseur) {
        }
        if ($objetInstance instanceof DocPiece) {
        }
        //GROUPE PARAMETRES
        if ($objetInstance instanceof Utilisateur) {
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Etapes(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_ETAPES_ID)->onlyOnIndex();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)->setColumns(6); //->onlyOnIndex();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR)->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE)->hideOnForm();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION)->hideOnForm();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION)->hideOnForm();
        }
        return $tabAttributs;
    }


    public function setCRM_Fields_Pistes(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_PISTE_ID)->onlyOnIndex();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)->setColumns(6);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF])) {
            $tabAttributs[] = TextField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)->setColumns(6);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_MONTANT])) {
            $tabAttributs[] = NumberField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)->setColumns(6);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_CONTACT])) {
            $tabAttributs[] = AssociationField::new('contact', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
                ->setColumns(6)
                ->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
            $tabAttributs[] = CollectionField::new('contact', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
                ->setColumns(6)
                ->onlyOnIndex();
            $tabAttributs[] = ArrayField::new('contact', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
                ->setColumns(6)
                ->onlyOnDetail();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_COTATION])) {
            $tabAttributs[] = AssociationField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)->setColumns(6)->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });

            $tabAttributs[] = CollectionField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)->setColumns(6)->onlyOnIndex();
            $tabAttributs[] = ArrayField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)->setColumns(6)->onlyOnDetail();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_ACTIONS])) {
            $tabAttributs[] = AssociationField::new('actions', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)->setColumns(6)->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
            $tabAttributs[] = CollectionField::new('actions', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)->setColumns(6)->onlyOnIndex();
            $tabAttributs[] = ArrayField::new('actions', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)->setColumns(6)->onlyOnDetail();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_ETAPE])) {
            $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)->setColumns(6)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION])){
            $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)->setColumns(6);
        }

        /* 
        //Ligne 01
        OK == TextField::new('nom', "Nom")->setColumns(6),
        OK ==TextField::new('objectif', "Objectif")->setColumns(6),

        //Ligne 02
        OK == NumberField::new('montant', "Revenu potentiel ($)")->setColumns(6),
        //AssociationField::new('contact', "Contacts")->hideOnIndex()->setColumns(6),
        OK == AssociationField::new('contact', "Contacts")->setColumns(6)->onlyOnForms()
        ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
            return $entityRepository
                ->createQueryBuilder('e')
                ->Where('e.entreprise = :ese')
                ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                ;
        })
        ,
        OK == CollectionField::new('contact', "Contacts")->setColumns(6)->onlyOnIndex(),
        OK == ArrayField::new('contact', "Contacts")->setColumns(6)->onlyOnDetail(),

        //Ligne 03
        
        OK == AssociationField::new('cotations', "Cotations")->setColumns(6)->onlyOnForms()
        ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
            return $entityRepository
                ->createQueryBuilder('e')
                ->Where('e.entreprise = :ese')
                ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                ;
        })
        ,
        OK == CollectionField::new('cotations', "Cotations")->setColumns(6)->onlyOnIndex(),
        OK == ArrayField::new('cotations', "Cotations")->setColumns(6)->onlyOnDetail(),
        
        OK == AssociationField::new('actions', "Missions")->setColumns(6)->onlyOnForms()
        ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
            return $entityRepository
                ->createQueryBuilder('e')
                ->Where('e.entreprise = :ese')
                ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                ;
        })
        ,
        OK == CollectionField::new('actions', "Missions")->setColumns(6)->onlyOnIndex(),
        OK == ArrayField::new('actions', "Missions")->setColumns(6)->onlyOnDetail(),

        //Ligne 04
        OK == AssociationField::new('etape', "Etape actuelle")->setColumns(6)
        ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
            return $entityRepository
                ->createQueryBuilder('e')
                ->Where('e.entreprise = :ese')
                ->setParameter('ese', $this->serviceEntreprise->getEntreprise())
                ;
        })
        ,
        OK == DateTimeField::new('expiredAt', "Echéance")->setColumns(6),

        //Ligne 05
        AssociationField::new('utilisateur', "Utilisateur")->setColumns(6)->hideOnForm()
        ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]),
        
        //AssociationField::new('entreprise', "Entreprise")->hideOnIndex()->setColumns(6),
        DateTimeField::new('createdAt', "Date création")->hideOnIndex()->hideOnForm(),
        DateTimeField::new('updatedAt', "Dernière modification")->hideOnForm(),

        //LES CHAMPS CALCULABLES
        FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')->onlyOnDetail(),
        //SECTION - PRIME
        FormField::addPanel('Primes')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),
        ArrayField::new('calc_polices_tab', "Polices")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_polices_primes_nette', "Prime nette")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_polices_fronting', "Fronting")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_polices_accessoire', "Accéssoires")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_polices_tva', "Taxes")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_polices_primes_totale', "Prime totale")->hideOnForm(),//->onlyOnDetail(),

        //SECTION - REVENU
        FormField::addPanel('Commissions')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),//<i class="fa-solid fa-toggle-off"></i>
        NumberField::new('calc_revenu_reserve', "Réserve")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_revenu_partageable', "Commissions partegeables")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_revenu_ht', "Commissions hors taxes")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_revenu_ttc', "Commissions ttc")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_revenu_ttc_encaisse', "Commissions encaissées")->hideOnForm(),//->onlyOnDetail(),
        ArrayField::new('calc_revenu_ttc_encaisse_tab_ref_factures', "Factures / Notes de débit")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_revenu_ttc_solde_restant_du', "Solde restant dû")->hideOnForm(),//->onlyOnDetail(),
        
        //SECTION - PARTENAIRES
        FormField::addPanel('Retrocommossions')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),
        NumberField::new('calc_retrocom', "Retrocommissions dûes")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_retrocom_payees', "Retrocommissions payées")->hideOnForm(),//->onlyOnDetail(),
        ArrayField::new('calc_retrocom_payees_tab_factures', "Factures / Notes de débit")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_retrocom_solde', "Solde restant dû")->hideOnForm(),//->onlyOnDetail(),

        //SECTION - TAXES
        FormField::addPanel('Impôts et Taxes')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),
        ArrayField::new('calc_taxes_courtier_tab', "Taxes concernées")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_taxes_courtier', "Montant dû")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_taxes_courtier_payees', "Montant payé")->hideOnForm(),//->onlyOnDetail(),
        ArrayField::new('calc_taxes_courtier_payees_tab_ref_factures', "Factures / Notes de débit")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_taxes_courtier_solde', "Solde restant dû")->hideOnForm(),//->onlyOnDetail(),

        FormField::addPanel()->onlyOnDetail(),
        ArrayField::new('calc_taxes_assureurs_tab', "Taxes concernées")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_taxes_assureurs', "Montant dû")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_taxes_assureurs_payees', "Montant payé")->hideOnForm(),//->onlyOnDetail(),
        ArrayField::new('calc_taxes_assureurs_payees_tab_ref_factures', "Factures / Notes de débit")->hideOnForm(),//->onlyOnDetail(),
        NumberField::new('calc_taxes_assureurs_solde', "Solde restant dû")->hideOnForm(),//->onlyOnDetail(),
         */





        return $tabAttributs;
    }

    public function appliquerPreferenceAttributs($objetInstance, $tabAttributs)
    {
        $preference = $this->chargerPreference($this->serviceEntreprise->getUtilisateur(), $this->serviceEntreprise->getEntreprise());
        //définition des attributs des pages
        $tabAttributs = $this->definirAttributsPages($objetInstance, $preference, $tabAttributs);
        return $tabAttributs;
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
        $preference->setCrmMissions([0, 1]);
        $preference->setCrmFeedbacks([0, 1]);
        $preference->setCrmCotations([0, 1]);
        $preference->setCrmEtapes([1, 2, 4, 5]); //ok
        $preference->setCrmPistes([0, 1]);
        //PRO
        $preference->setProTaille(100);
        $preference->setProAssureurs([0, 1]);
        $preference->setProAutomobiles([0, 1]);
        $preference->setProContacts([0, 1]);
        $preference->setProClients([0, 1]);
        $preference->setProPartenaires([0, 1]);
        $preference->setProPolices([0, 1]);
        $preference->setProProduits([0, 1]);
        //FIN
        $preference->setFinTaille(100);
        $preference->setFinTaxes([0, 1]);
        $preference->setFinMonnaies([0, 1]);
        $preference->setFinCommissionsPayees([0, 1]);
        $preference->setFinRetrocommissionsPayees([0, 1]);
        $preference->setFinTaxesPayees([0, 1]);
        //SIN
        $preference->setSinTaille(100);
        $preference->setSinCommentaires([0, 1]);
        $preference->setSinEtapes([0, 1]);
        $preference->setSinSinistres([0, 1]);
        $preference->setSinVictimes([0, 1]);
        //BIB
        $preference->setBibTaille(100);
        $preference->setBibCategories([0, 1]);
        $preference->setBibClasseurs([0, 1]);
        $preference->setBibPieces([0, 1]);
        //PAR
        $preference->setParTaille(100);
        $preference->setParUtilisateurs([0, 1]);

        //persistance
        $this->entityManager->persist($preference);
        $this->entityManager->flush();
    }
}
