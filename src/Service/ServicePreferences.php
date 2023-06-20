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
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
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
        //GROUPE CRM
        if ($instance instanceof Action) {
            $this->setTailleCRM($preference, $crud);
        }
        if ($instance instanceof FeedbackCRM) {
            $this->setTailleCRM($preference, $crud);
        }
        if ($instance instanceof Cotation) {
            $this->setTailleCRM($preference, $crud);
        }
        if ($instance instanceof EtapeCrm) {
            $this->setTailleCRM($preference, $crud);
        }
        if ($instance instanceof Piste) {
            $this->setTailleCRM($preference, $crud);
        }
        //GROUPE PRODUCTION
        if ($instance instanceof Assureur) {
            $this->setTaillePRO($preference, $crud);
        }
        if ($instance instanceof Automobile) {
            $this->setTaillePRO($preference, $crud);
        }
        if ($instance instanceof Contact) {
            $this->setTaillePRO($preference, $crud);
        }
        if ($instance instanceof Client) {
            $this->setTaillePRO($preference, $crud);
        }
        if ($instance instanceof Partenaire) {
            $this->setTaillePRO($preference, $crud);
        }
        if ($instance instanceof Police) {
            $this->setTaillePRO($preference, $crud);
        }
        if ($instance instanceof Produit) {
            $this->setTaillePRO($preference, $crud);
        }
        //GROUPE FINANCE
        if ($instance instanceof Taxe) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof Monnaie) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof PaiementCommission) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof PaiementPartenaire) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof PaiementTaxe) {
            $this->setTailleFIN($preference, $crud);
        }
        //GROUPE SINISTRE
        if ($instance instanceof CommentaireSinistre) {
            $this->setTailleSIN($preference, $crud);
        }
        if ($instance instanceof EtapeSinistre) {
            $this->setTailleSIN($preference, $crud);
        }
        if ($instance instanceof Expert) {
            $this->setTailleSIN($preference, $crud);
        }
        if ($instance instanceof Sinistre) {
            $this->setTailleSIN($preference, $crud);
        }
        if ($instance instanceof Victime) {
            $this->setTailleSIN($preference, $crud);
        }
        //GROUPE BIBLIOTHEQUE
        if ($instance instanceof DocCategorie) {
            $this->setTailleBIB($preference, $crud);
        }
        if ($instance instanceof DocClasseur) {
            $this->setTailleBIB($preference, $crud);
        }
        if ($instance instanceof DocPiece) {
            $this->setTailleBIB($preference, $crud);
        }
        //GROUPE PARAMETRES
        if ($instance instanceof Utilisateur) {
            $this->setTaillePAR($preference, $crud);
        }
    }

    public function setTailleCRM(Preference $preference, Crud $crud){
        $taille = 100;
        if ($preference->getCrmTaille() != 0) {
            $taille = $preference->getCrmTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTaillePRO(Preference $preference, Crud $crud){
        $taille = 100;
        if ($preference->getProTaille() != 0) {
            $taille = $preference->getProTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTailleFIN(Preference $preference, Crud $crud){
        $taille = 100;
        if ($preference->getFinTaille() != 0) {
            $taille = $preference->getFinTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTailleSIN(Preference $preference, Crud $crud){
        $taille = 100;
        if ($preference->getSinTaille() != 0) {
            $taille = $preference->getSinTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTailleBIB(Preference $preference, Crud $crud){
        $taille = 100;
        if ($preference->getBibTaille() != 0) {
            $taille = $preference->getBibTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTaillePAR(Preference $preference, Crud $crud){
        $taille = 100;
        if ($preference->getParTaille() != 0) {
            $taille = $preference->getParTaille();
        }
        $crud->setPaginatorPageSize($taille);
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
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR])){
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR)->setColumns(6)->hideOnForm()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION])){
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION)->hideOnIndex()->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION])){
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)->hideOnForm();
        }
        //LES CHAMPS CALCULABLES
        $tabAttributs[] = FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')->onlyOnDetail();
        $tabAttributs[] = FormField::addPanel('Primes')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail();
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_tab])){
            $tabAttributs[] = ArrayField::new('calc_polices_tab', PreferenceCrudController::PREF_calc_polices_tab)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_primes_nette])){
            $tabAttributs[] = NumberField::new('calc_polices_primes_nette', PreferenceCrudController::PREF_calc_polices_primes_nette)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_fronting])){
            $tabAttributs[] = NumberField::new('calc_polices_fronting', PreferenceCrudController::PREF_calc_polices_fronting)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_accessoire])){
            $tabAttributs[] = NumberField::new('calc_polices_accessoire', PreferenceCrudController::PREF_calc_polices_accessoire)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_tva])){
            $tabAttributs[] = NumberField::new('calc_polices_tva', PreferenceCrudController::PREF_calc_polices_tva)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_primes_totale])){
            $tabAttributs[] = NumberField::new('calc_polices_primes_totale', PreferenceCrudController::PREF_calc_polices_primes_totale)->hideOnForm();//->onlyOnDetail(),
        }
        //SECTION REVENU
        $tabAttributs[] = FormField::addPanel('Commissions')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail();//<i class="fa-solid fa-toggle-off"></i>
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_reserve])){
            $tabAttributs[] = NumberField::new('calc_revenu_reserve', PreferenceCrudController::PREF_calc_revenu_reserve)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_partageable])){
            $tabAttributs[] = NumberField::new('calc_revenu_partageable', PreferenceCrudController::PREF_calc_revenu_partageable)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ht])){
            $tabAttributs[] = NumberField::new('calc_revenu_ht', PreferenceCrudController::PREF_calc_revenu_ht)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc])){
            $tabAttributs[] = NumberField::new('calc_revenu_ttc', PreferenceCrudController::PREF_calc_revenu_ttc)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc_encaisse])){
            $tabAttributs[] = NumberField::new('calc_revenu_ttc_encaisse', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc_encaisse_tab_ref_factures])){
            $tabAttributs[] = ArrayField::new('calc_revenu_ttc_encaisse_tab_ref_factures', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse_tab_ref_factures)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du])){
            $tabAttributs[] = NumberField::new('calc_revenu_ttc_solde_restant_du', PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du)->hideOnForm();//->onlyOnDetail(),
        }
        //SECTION PARTENAIRES
        $tabAttributs[] = FormField::addPanel('Retrocommossions')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail();
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom])){
            $tabAttributs[] = NumberField::new('calc_retrocom', PreferenceCrudController::PREF_calc_retrocom)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom_payees])){
            $tabAttributs[] = NumberField::new('calc_retrocom_payees', PreferenceCrudController::PREF_calc_retrocom_payees)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom_payees_tab_factures])){
            $tabAttributs[] = ArrayField::new('calc_retrocom_payees_tab_factures', PreferenceCrudController::PREF_calc_retrocom_payees_tab_factures)->hideOnForm();//->onlyOnDetail(),
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom_solde])){
            $tabAttributs[] = NumberField::new('calc_retrocom_solde', PreferenceCrudController::PREF_calc_retrocom_solde)->hideOnForm();//->onlyOnDetail(),
        }

        /* 
        
        //SECTION - PARTENAIRES
        OK == FormField::addPanel('Retrocommossions')->setIcon('fa-solid fa-toggle-off')->onlyOnDetail(),
        OK == NumberField::new('calc_retrocom', "Retrocommissions dûes")->hideOnForm(),//->onlyOnDetail(),
        OK == NumberField::new('calc_retrocom_payees', "Retrocommissions payées")->hideOnForm(),//->onlyOnDetail(),
        OK == ArrayField::new('calc_retrocom_payees_tab_factures', "Factures / Notes de débit")->hideOnForm(),//->onlyOnDetail(),
        OK == NumberField::new('calc_retrocom_solde', "Solde restant dû")->hideOnForm(),//->onlyOnDetail(),

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
