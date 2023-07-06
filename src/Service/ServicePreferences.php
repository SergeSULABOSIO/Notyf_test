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
use PhpParser\Node\Expr\Cast\Array_;
use Doctrine\ORM\EntityManagerInterface;
use function PHPUnit\Framework\returnSelf;
use phpDocumentor\Reflection\Types\Boolean;
use App\Controller\Admin\ClientCrudController;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\ProduitCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use App\Controller\Admin\ActionCRMCrudController;
use App\Controller\Admin\AutomobileCrudController;
use App\Controller\Admin\PreferenceCrudController;
use App\Controller\Admin\TaxeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ServicePreferences
{
    private $preferences;
    private $taxes = [];
    public const INDICE_TAXE_COURTIER = 0;
    public const INDICE_TAXE_ASSUREUR = 1;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise
    ) {
    }

    public function chargerTaxes()
    {
        $this->taxes = $this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
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

    public function setTailleCRM(Preference $preference, Crud $crud)
    {
        $taille = 100;
        if ($preference->getCrmTaille() != 0) {
            $taille = $preference->getCrmTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTaillePRO(Preference $preference, Crud $crud)
    {
        $taille = 100;
        if ($preference->getProTaille() != 0) {
            $taille = $preference->getProTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTailleFIN(Preference $preference, Crud $crud)
    {
        $taille = 100;
        if ($preference->getFinTaille() != 0) {
            $taille = $preference->getFinTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTailleSIN(Preference $preference, Crud $crud)
    {
        $taille = 100;
        if ($preference->getSinTaille() != 0) {
            $taille = $preference->getSinTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTailleBIB(Preference $preference, Crud $crud)
    {
        $taille = 100;
        if ($preference->getBibTaille() != 0) {
            $taille = $preference->getBibTaille();
        }
        $crud->setPaginatorPageSize($taille);
    }

    public function setTaillePAR(Preference $preference, Crud $crud)
    {
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

    public function definirAttributsPages($objetInstance, Preference $preference)
    {
        //GROUPE CRM
        if ($objetInstance instanceof ActionCRM) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-paper-plane') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une mission est une ou un ensembles d'actions attribuée(s) à un ou plusieurs utilisateurs.")
            ];
            $tabAttributs = $this->setCRM_Fields_Action_Index_Details($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Action_form($tabAttributs);
        }
        if ($objetInstance instanceof FeedbackCRM) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-comments') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Un feedback est une réponse ou compte rendu attaché à une mission. Chaque mission doit avoir un ou plusieurs feedbacks.")
            ];
            $tabAttributs = $this->setCRM_Fields_Feedback_Index_Details($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Feedback_form($tabAttributs);
        }
        if ($objetInstance instanceof Cotation) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-cash-register') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une cotation est tout simplement un dévis/une offre financière relative à un risque précis. Ce n'est pas une police d'assurance.")
            ];
            $tabAttributs = $this->setCRM_Fields_Cotation_Index_Details($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Cotation_form($tabAttributs);
        }
        if ($objetInstance instanceof EtapeCrm) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-list-check') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une étape (ou phase) dans le traitement d'une pistre. Le traitement d'une piste (càd sa conversion en client) est un processus qui peut passer par un certain nombre d'étapes.")
            ];
            $tabAttributs = $this->setCRM_Fields_Etapes_Index_Details($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Etapes_form($tabAttributs);
        }
        if ($objetInstance instanceof Piste) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-location-crosshairs') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une piste est un prospect (ou client potientiel) à suivre stratégiquement afin de lui convertir en client."),
            ];
            $tabAttributs = $this->setCRM_Fields_Pistes_Index_Details($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Pistes_form($tabAttributs);
        }
        //GROUPE PRODUCTION
        if ($objetInstance instanceof Assureur) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-umbrella') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le preneur des risques en contre partie du versement d'une prime d'assurance et selon les condtions bien spécifiées dans la police.")
            ];
            $tabAttributs = $this->setCRM_Fields_Assureur_Index_Details($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Assureur_form($tabAttributs);
        }
        if ($objetInstance instanceof Automobile) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-car') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Engin auto-moteur.")
            ];
            $tabAttributs = $this->setCRM_Fields_Engins_Index_Details($preference->getProAutomobiles(), PreferenceCrudController::TAB_PRO_ENGINS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Engins_form($tabAttributs);
        }
        if ($objetInstance instanceof Contact) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-address-book') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Tout simple un contact au sens littéral du terme. Une personne à contacter dans le cadre des assurances."),
            ];
            $tabAttributs = $this->setCRM_Fields_Contacts_Index_Details($preference->getProContacts(), PreferenceCrudController::TAB_PRO_CONTACTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Contacts_form($tabAttributs);
        }
        if ($objetInstance instanceof Client) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-person-shelter') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le client c'est l'assuré ou le bénéficiaire de la couverture d'assurance.")
            ];
            $tabAttributs = $this->setCRM_Fields_Clients_Index_Details($preference->getProClients(), PreferenceCrudController::TAB_PRO_CLIENTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Clients_form($tabAttributs);
        }
        if ($objetInstance instanceof Partenaire) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-handshake') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le partenaire ou intermédiaire à travers lequel un client peut être acquis.")
            ];
            $tabAttributs = $this->setCRM_Fields_Partenaires_Index_Details($preference->getProPartenaires(), PreferenceCrudController::TAB_PRO_PARTENAIRES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Partenaires_form($tabAttributs);
        }
        if ($objetInstance instanceof Police) {
            $tabAttributs = [
                FormField::addTab(' Informations de base')
                    ->setIcon('fas fa-file-shield') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le contrat d'assurance en place.")
            ];
            $tabAttributs = $this->setCRM_Fields_Polices_Index_Details($preference->getProPolices(), PreferenceCrudController::TAB_PRO_POLICES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Polices_form($tabAttributs);
        }
        if ($objetInstance instanceof Produit) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-gifts') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une couverture d'assurance.")
            ];
            $tabAttributs = $this->setCRM_Fields_Produits_Index_Details($preference->getProProduits(), PreferenceCrudController::TAB_PRO_PRODUITS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Produits_form($tabAttributs);
        }
        //GROUPE FINANCES
        if ($objetInstance instanceof Taxe) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-landmark-dome') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Taxes ou Impôts dûes aux autorités étatiques.")
            ];
            $tabAttributs = $this->setCRM_Fields_Taxes_Index_Details($preference->getFinTaxes(), PreferenceCrudController::TAB_FIN_TAXES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Taxes_form($tabAttributs);
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

    public function setCRM_Fields_Polices_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_POLICE_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT])) {
            $tabAttributs[] = NumberField::new('idavenant', PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT])) {
            $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
                ->hideOnForm()
                ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_REFERENCE])) {
            $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CLIENT])) {
            $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PRODUIT])) {
            $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS])) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS])) {
            $tabAttributs[] = TextField::new('reassureurs', PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION])) {
            $tabAttributs[] = DateTimeField::new('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION])) {
            $tabAttributs[] = DateTimeField::new('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET])) {
            $tabAttributs[] = DateTimeField::new('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION])) {
            $tabAttributs[] = DateTimeField::new('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_PRO_POLICE_PISTE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE])) {
            $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_REMARQUE])) {
            $tabAttributs[] = TextareaField::new('remarques', PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_POLICE_ENTREPRISE)
                ->hideOnForm();
        }
        //Onglet Prime & Capitaux
        $tabAttributs[] = FormField::addTab(' Prime & Capitaux')
            ->setIcon('fas fa-bag-shopping')
            ->setHelp("Le contrat d'assurance en place.")
            ->hideOnForm();
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_MONNAIE])) {
            $tabAttributs[] = AssociationField::new('monnaie', PreferenceCrudController::PREF_PRO_POLICE_MONNAIE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CAPITAL])) {
            $tabAttributs[] = NumberField::new('capital', PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT])) {
            $tabAttributs[] = ChoiceField::new('modepaiement', PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
                ->hideOnForm()
                ->setChoices(PoliceCrudController::TAB_POLICE_MODE_PAIEMENT);
        }
        $tabAttributs[] = FormField::addPanel('Facture client')->hideOnForm();
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE])) {
            $tabAttributs[] = NumberField::new('primenette', PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRONTING])) {
            $tabAttributs[] = NumberField::new('fronting', PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ARCA])) {
            $tabAttributs[] = NumberField::new('arca', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_COURTIER, PreferenceCrudController::PREF_PRO_POLICE_ARCA))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_TVA])) {
            $tabAttributs[] = NumberField::new('tva', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_ASSUREUR, PreferenceCrudController::PREF_PRO_POLICE_TVA))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN])) {
            $tabAttributs[] = NumberField::new('fraisadmin', PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT])) {
            $tabAttributs[] = NumberField::new('discount', PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE])) {
            $tabAttributs[] = NumberField::new('primetotale', PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
                ->hideOnForm();
        }

        //Onglet Structure des revenus
        $tabAttributs[] = FormField::addTab(' Structure des revenus')->setIcon('fas fa-sack-dollar')->hideOnForm();
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE])) {
            $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE])) {
            $tabAttributs[] = PercentField::new('partExceptionnellePartenaire', PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
                ->hideOnForm();
        }
        $tabAttributs[] = FormField::addPanel('Commission de réassurance')->hideOnForm();
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_RI_COM])) {
            $tabAttributs[] = NumberField::new('ricom', PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM])) {
            $tabAttributs[] = ChoiceField::new('cansharericom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
                ->hideOnForm()
                ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY])) {
            $tabAttributs[] = ChoiceField::new('ricompayableby', PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
                ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
                ->hideOnForm();
        }
        $tabAttributs[] = FormField::addPanel("Commission locale")->hideOnForm();
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM])) {
            $tabAttributs[] = NumberField::new('localcom', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM])) {
            $tabAttributs[] = ChoiceField::new('cansharelocalcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
                ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY])) {
            $tabAttributs[] = ChoiceField::new('localcompayableby', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
                ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
                ->hideOnForm();
        }
        $tabAttributs[] = FormField::addPanel("Commission sur Fronting")->hideOnForm();
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM])) {
            $tabAttributs[] = NumberField::new('frontingcom', PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM])) {
            $tabAttributs[] = ChoiceField::new('cansharefrontingcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
                ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY])) {
            $tabAttributs[] = ChoiceField::new('frontingcompayableby', PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
                ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
                ->hideOnForm();
        }

        //Onglet Documents
        $tabAttributs[] = FormField::addTab(' Documents')->setIcon('fas fa-book')->hideOnForm();
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PIECES])) {
            $tabAttributs[] = AssociationField::new('pieces', PreferenceCrudController::PREF_PRO_POLICE_PIECES)
                ->hideOnForm();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables(true, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }


    public function setCRM_Fields_Produits_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_PRODUIT_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_CODE])) {
            $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION])) {
            $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION])) {
            $tabAttributs[] = PercentField::new('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE])) {
            $tabAttributs[] = ChoiceField::new('isobligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_IS_OBLIGATOIRE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT])) {
            $tabAttributs[] = ChoiceField::new('isabonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_IS_ABONNEMENT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT])) {
            $tabAttributs[] = ChoiceField::new('categorie', PreferenceCrudController::PREF_PRO_PRODUIT_CATEGORIE)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_CATEGORIE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PRODUIT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PRODUIT_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION)
                ->hideOnForm();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Taxes_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_TAXE_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_TAXE_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_TAUX])) {
            $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_FIN_TAXE_TAUX)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION])) {
            $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION])) {
            $tabAttributs[] = TextField::new('organisation', PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER])) {
            $tabAttributs[] = ChoiceField::new('payableparcourtier', PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER)
                ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_TAXE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_TAXE_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_DERNIERE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_TAXE_DERNIERE_MODIFICATION)
                ->hideOnForm();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_CalculablesTaxes($tabAttributs, $tabPreferences, $tabDefaultAttributs);
        return $tabAttributs;
    }

    public function setCRM_Fields_Polices_form($tabAttributs)
    {

        $tabAttributs[] = NumberField::new('idavenant', PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
            ->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            ->setColumns(4)
            ->onlyOnForms()
            ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT);
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = FormField::addPanel('')
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
            ->onlyOnForms()
            ->setRequired(false)
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = TextField::new('reassureurs', PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = DateTimeField::new('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = DateTimeField::new('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = DateTimeField::new('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = DateTimeField::new('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_PRO_POLICE_PISTE)
            ->onlyOnForms()
            ->setRequired(false)
            ->setColumns(4)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = FormField::addTab(' Prime & Capitaux')
            ->setIcon('fas fa-bag-shopping')
            ->setHelp("Le contrat d'assurance en place.")
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('monnaie', PreferenceCrudController::PREF_PRO_POLICE_MONNAIE)
            ->onlyOnForms()
            ->setColumns(3)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = NumberField::new('capital', PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = ChoiceField::new('modepaiement', PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            ->setColumns(2)
            ->onlyOnForms()
            ->setChoices(PoliceCrudController::TAB_POLICE_MODE_PAIEMENT);
        $tabAttributs[] = FormField::addPanel('Facture client')->onlyOnForms();
        $tabAttributs[] = NumberField::new('primenette', PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            ->onlyOnForms()
            ->setColumns(1);
        $tabAttributs[] = NumberField::new('fronting', PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            ->onlyOnForms()
            ->setColumns(1);
        $tabAttributs[] = NumberField::new('arca', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_COURTIER, PreferenceCrudController::PREF_PRO_POLICE_ARCA))
            ->onlyOnForms()
            ->setColumns(1);
        $tabAttributs[] = NumberField::new('tva', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_ASSUREUR, PreferenceCrudController::PREF_PRO_POLICE_TVA))
            ->onlyOnForms()
            ->setColumns(1);
        $tabAttributs[] = NumberField::new('fraisadmin', PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            ->onlyOnForms()
            ->setColumns(1);
        $tabAttributs[] = NumberField::new('discount', PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            ->onlyOnForms()
            ->setColumns(1);
        $tabAttributs[] = NumberField::new('primetotale', PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            ->onlyOnForms()
            ->setColumns(1);
        $tabAttributs[] = FormField::addTab(' Structure des revenus')
            ->setIcon('fas fa-sack-dollar')
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
            ->onlyOnForms()
            ->setRequired(false)
            ->setColumns(4)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = PercentField::new('partExceptionnellePartenaire', PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            ->onlyOnForms()
            ->setColumns(3)
            ->setHelp("Précisez le taux exceptionnel si, pour ce compte spécifique, le taux est différent du standard.");
        $tabAttributs[] = FormField::addPanel('Commission de réassurance')
            ->onlyOnForms();
        $tabAttributs[] = NumberField::new('ricom', "Montant ht")
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = ChoiceField::new('cansharericom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            ->onlyOnForms()
            ->setColumns(2)
            ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON);
        $tabAttributs[] = ChoiceField::new('ricompayableby', PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            ->onlyOnForms()
            ->setColumns(3)
            ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR);
        $tabAttributs[] = FormField::addPanel("Commission locale")->onlyOnForms();
        $tabAttributs[] = NumberField::new('localcom', "Montant ht")
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = ChoiceField::new('cansharelocalcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            ->onlyOnForms()
            ->setColumns(2)
            ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON);
        $tabAttributs[] = ChoiceField::new('localcompayableby', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            ->onlyOnForms()
            ->setColumns(3)
            ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR);
        $tabAttributs[] = FormField::addPanel("Commission sur Fronting")
            ->onlyOnForms();
        $tabAttributs[] = NumberField::new('frontingcom', "Montant ht")
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = ChoiceField::new('cansharefrontingcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            ->onlyOnForms()
            ->setColumns(2)
            ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON);
        $tabAttributs[] = ChoiceField::new('frontingcompayableby', PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            ->onlyOnForms()
            ->setColumns(3)
            ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR);
        $tabAttributs[] = TextareaField::new('remarques', PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
            ->onlyOnForms()
            ->setColumns(12);

        /* $tabAttributs[] = FormField::addTab(' Documents')
            ->setIcon('fas fa-book')
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('pieces', "Documents")->setColumns(12)->onlyOnForms()
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            }); */

        return $tabAttributs;
    }

    public function setCRM_Fields_Produits_form($tabAttributs)
    {

        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = PercentField::new('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
            ->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('isobligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
            ->setColumns(1)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_IS_OBLIGATOIRE)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('isabonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
            ->setColumns(1)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_IS_ABONNEMENT)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('categorie', PreferenceCrudController::PREF_PRO_PRODUIT_CATEGORIE)
            ->setColumns(2)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_CATEGORIE)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_Taxes_form($tabAttributs)
    {

        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_TAXE_NOM)
            ->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_FIN_TAXE_TAUX)
            ->setColumns(1)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION)
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('organisation', PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION)
            ->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('payableparcourtier', PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER)
            ->setColumns(2)
            ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_Partenaires_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = NumberField::new('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)
            ->setColumns(6)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_Partenaires_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_PARTENAIRE_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_PART])) {
            $tabAttributs[] = NumberField::new('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM])) {
            $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT])) {
            $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT])) {
            $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PARTENAIRE_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PARTENAIRE_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION)
                ->hideOnForm();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Clients_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_CLIENT_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CLIENT_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE])) {
            $tabAttributs[] = ChoiceField::new('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
                ->hideOnForm()
                ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_RCCM])) {
            $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_CLIENT_RCCM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_IDNAT])) {
            $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_CLIENT_IDNAT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT])) {
            $tabAttributs[] = TextField::new('numipot', PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR])) {
            $tabAttributs[] = ChoiceField::new('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
                ->hideOnForm()
                ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION)
                ->hideOnForm();
        }
        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Clients_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CLIENT_NOM)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
            ->setColumns(6)
            ->onlyOnForms()
            ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE);
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_CLIENT_RCCM)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_CLIENT_IDNAT)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('numipot', PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
            ->setColumns(6)
            ->onlyOnForms()
            ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR);

        return $tabAttributs;
    }

    public function setCRM_Fields_Engins_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('plaque', PreferenceCrudController::PREF_PRO_ENGIN_N°_PLAQUE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('chassis', PreferenceCrudController::PREF_PRO_ENGIN_N°_CHASSIS)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('model', PreferenceCrudController::PREF_PRO_ENGIN_MODEL)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('marque', PreferenceCrudController::PREF_PRO_ENGIN_MARQUE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('annee', PreferenceCrudController::PREF_PRO_ENGIN_ANNEE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('puissance', PreferenceCrudController::PREF_PRO_ENGIN_PUISSANCE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = NumberField::new('valeur', PreferenceCrudController::PREF_PRO_ENGIN_VALEUR)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = AssociationField::new('monnaie', PreferenceCrudController::PREF_PRO_ENGIN_MONNAIE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = NumberField::new('nbsieges', PreferenceCrudController::PREF_PRO_ENGIN_NB_SIEGES)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = ChoiceField::new('utilite', PreferenceCrudController::PREF_PRO_ENGIN_USAGE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setChoices(AutomobileCrudController::TAB_AUTO_UTILITE);
        $tabAttributs[] = ChoiceField::new('nature', PreferenceCrudController::PREF_PRO_ENGIN_NATURE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setChoices(AutomobileCrudController::TAB_AUTO_NATURE);

        return $tabAttributs;
    }

    public function setCRM_Fields_Contacts_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_CONTACT_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CONTACT_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_POSTE])) {
            $tabAttributs[] = TextField::new('poste', PreferenceCrudController::PREF_PRO_CONTACT_POSTE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CONTACT_EMAIL)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_CLIENT])) {
            $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_PRO_CONTACT_CLIENT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_CONTACT_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_CONTACT_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_MODIFICATION)
                ->hideOnForm();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_Contacts_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CONTACT_NOM)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('poste', PreferenceCrudController::PREF_PRO_CONTACT_POSTE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CONTACT_EMAIL)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_PRO_CONTACT_CLIENT)
            ->onlyOnForms()
            ->setColumns(6);

        return $tabAttributs;
    }

    public function setCRM_Fields_Engins_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ENGIN_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_N°_PLAQUE])) {
            $tabAttributs[] = TextField::new('plaque', PreferenceCrudController::PREF_PRO_ENGIN_N°_PLAQUE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_N°_CHASSIS])) {
            $tabAttributs[] = TextField::new('chassis', PreferenceCrudController::PREF_PRO_ENGIN_N°_CHASSIS)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_MODEL])) {
            $tabAttributs[] = TextField::new('model', PreferenceCrudController::PREF_PRO_ENGIN_MODEL)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_MARQUE])) {
            $tabAttributs[] = TextField::new('marque', PreferenceCrudController::PREF_PRO_ENGIN_MARQUE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_ANNEE])) {
            $tabAttributs[] = TextField::new('annee', PreferenceCrudController::PREF_PRO_ENGIN_ANNEE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_PUISSANCE])) {
            $tabAttributs[] = TextField::new('puissance', PreferenceCrudController::PREF_PRO_ENGIN_PUISSANCE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_MONNAIE])) {
            $tabAttributs[] = AssociationField::new('monnaie', PreferenceCrudController::PREF_PRO_ENGIN_MONNAIE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_VALEUR])) {
            $tabAttributs[] = NumberField::new('valeur', PreferenceCrudController::PREF_PRO_ENGIN_VALEUR)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_NB_SIEGES])) {
            $tabAttributs[] = NumberField::new('nbsieges', PreferenceCrudController::PREF_PRO_ENGIN_NB_SIEGES)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_USAGE])) {
            $tabAttributs[] = ChoiceField::new('utilite', PreferenceCrudController::PREF_PRO_ENGIN_USAGE)
                ->hideOnForm()
                ->setChoices(AutomobileCrudController::TAB_AUTO_UTILITE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_NATURE])) {
            $tabAttributs[] = ChoiceField::new('nature', PreferenceCrudController::PREF_PRO_ENGIN_NATURE)
                ->hideOnForm()
                ->setChoices(AutomobileCrudController::TAB_AUTO_NATURE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_POLICE])) {
            $tabAttributs[] = ArrayField::new('polices', PreferenceCrudController::PREF_PRO_ENGIN_POLICE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ENGIN_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_ENGIN_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_MODIFICATION)
                ->hideOnForm();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_Assureur_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_ASSUREUR_NOM)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = ChoiceField::new('isreassureur', PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR)
            ->onlyOnForms()
            ->setColumns(6)
            ->setChoices([
                'Réassureur' => 1,
                'Assureur' => 0
            ]);
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('licence', PreferenceCrudController::PREF_PRO_ASSUREUR_LICENCE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_ASSUREUR_IDNAT)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_ASSUREUR_NUM_IMPOT)
            ->onlyOnForms()
            ->setColumns(6);

        return $tabAttributs;
    }

    public function setCRM_Fields_Assureur_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ASSUREUR_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_ASSUREUR_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR])) {
            $tabAttributs[] = ChoiceField::new('isreassureur', PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR)
                ->hideOnForm()
                ->setChoices([
                    'Réassureur' => 1,
                    'Assureur' => 0
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM])) {
            $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_LICENCE])) {
            $tabAttributs[] = TextField::new('licence', PreferenceCrudController::PREF_PRO_ASSUREUR_LICENCE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_IDNAT])) {
            $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_ASSUREUR_IDNAT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_NUM_IMPOT])) {
            $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_ASSUREUR_NUM_IMPOT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ASSUREUR_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_ASSUREUR_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_CREATION)
                ->hideOnform();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION)
                ->hideOnform();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setAttributs_Calculables($isPolice, array $tabAttributs, array $tabPreferences, array $tabIndiceAttribut)
    {
        //LES CHAMPS CALCULABLES
        $tabAttributs[] = FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')
            ->hideOnForm();

        if ($isPolice == false) {
            $tabAttributs[] = FormField::addPanel('Primes')->setIcon('fa-solid fa-toggle-off')
                ->hideOnForm();
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_tab])) {
                $tabAttributs[] = ArrayField::new('calc_polices_tab', PreferenceCrudController::PREF_calc_polices_tab)
                    ->hideOnForm();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_primes_nette])) {
                $tabAttributs[] = NumberField::new('calc_polices_primes_nette', PreferenceCrudController::PREF_calc_polices_primes_nette)
                    ->hideOnForm();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_fronting])) {
                $tabAttributs[] = NumberField::new('calc_polices_fronting', PreferenceCrudController::PREF_calc_polices_fronting)
                    ->hideOnForm();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_accessoire])) {
                $tabAttributs[] = NumberField::new('calc_polices_accessoire', PreferenceCrudController::PREF_calc_polices_accessoire)
                    ->hideOnForm();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_tva])) {
                $tabAttributs[] = NumberField::new('calc_polices_tva', PreferenceCrudController::PREF_calc_polices_tva)
                    ->hideOnForm();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_primes_totale])) {
                $tabAttributs[] = NumberField::new('calc_polices_primes_totale', PreferenceCrudController::PREF_calc_polices_primes_totale)
                    ->hideOnForm();
            }
        }

        //SECTION REVENU
        $tabAttributs[] = FormField::addPanel('Commissions')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_reserve])) {
            $tabAttributs[] = NumberField::new('calc_revenu_reserve', PreferenceCrudController::PREF_calc_revenu_reserve)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_partageable])) {
            $tabAttributs[] = NumberField::new('calc_revenu_partageable', PreferenceCrudController::PREF_calc_revenu_partageable)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ht])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ht', PreferenceCrudController::PREF_calc_revenu_ht)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ttc])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ttc', PreferenceCrudController::PREF_calc_revenu_ttc)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ttc_encaisse])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ttc_encaisse', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ttc_encaisse_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_revenu_ttc_encaisse_tab_ref_factures', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse_tab_ref_factures)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ttc_solde_restant_du', PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du)
                ->hideOnForm();
        }
        //SECTION PARTENAIRES
        $tabAttributs[] = FormField::addPanel('Retrocommossions')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_retrocom])) {
            $tabAttributs[] = NumberField::new('calc_retrocom', PreferenceCrudController::PREF_calc_retrocom)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_retrocom_payees])) {
            $tabAttributs[] = NumberField::new('calc_retrocom_payees', PreferenceCrudController::PREF_calc_retrocom_payees)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_retrocom_payees_tab_factures])) {
            $tabAttributs[] = ArrayField::new('calc_retrocom_payees_tab_factures', PreferenceCrudController::PREF_calc_retrocom_payees_tab_factures)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_retrocom_solde])) {
            $tabAttributs[] = NumberField::new('calc_retrocom_solde', PreferenceCrudController::PREF_calc_retrocom_solde)
                ->hideOnForm();
        }
        //SECTION - TAXES
        $tabAttributs[] = FormField::addPanel('Impôts et Taxes')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_courtier_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Desc", PreferenceCrudController::PREF_calc_taxes_courtier_tab)) //
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_courtier))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Pymnt", PreferenceCrudController::PREF_calc_taxes_courtier_payees))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_payees_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_courtier_payees_tab_ref_factures', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "PdP", PreferenceCrudController::PREF_calc_taxes_courtier_payees_tab_ref_factures))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Solde", PreferenceCrudController::PREF_calc_taxes_courtier_solde))
                ->hideOnForm();
        }
        //SECTION - TAXES
        $tabAttributs[] = FormField::addPanel()
            ->hideOnForm();

        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Desc", PreferenceCrudController::PREF_calc_taxes_assureurs_tab))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_assureurs))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Pymnt", PreferenceCrudController::PREF_calc_taxes_assureurs_payees))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_payees_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_payees_tab_ref_factures', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "PdP", PreferenceCrudController::PREF_calc_taxes_assureurs_payees_tab_ref_factures))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Solde", PreferenceCrudController::PREF_calc_taxes_assureurs_solde))
                ->hideOnForm();
        }
        return $tabAttributs;
    }

    public function setAttributs_CalculablesTaxes(array $tabAttributs, array $tabPreferences, array $tabIndiceAttribut)
    {
        //LES CHAMPS CALCULABLES
        $tabAttributs[] = FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')
            ->hideOnForm();

        //SECTION - TAXES - COURTIER
        $tabAttributs[] = FormField::addPanel('Impôts et Taxes')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_courtier_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Desc", PreferenceCrudController::PREF_calc_taxes_courtier_tab)) //
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_courtier))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Pymnt", PreferenceCrudController::PREF_calc_taxes_courtier_payees))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_payees_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_courtier_payees_tab_ref_factures', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "PdP", PreferenceCrudController::PREF_calc_taxes_courtier_payees_tab_ref_factures))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Solde", PreferenceCrudController::PREF_calc_taxes_courtier_solde))
                ->hideOnForm();
        }
        //SECTION - TAXES ASSUREURS
        $tabAttributs[] = FormField::addPanel()
            ->hideOnForm();

        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Desc", PreferenceCrudController::PREF_calc_taxes_assureurs_tab))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_assureurs))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Pymnt", PreferenceCrudController::PREF_calc_taxes_assureurs_payees))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_payees_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_payees_tab_ref_factures', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "PdP", PreferenceCrudController::PREF_calc_taxes_assureurs_payees_tab_ref_factures))
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Solde", PreferenceCrudController::PREF_calc_taxes_assureurs_solde))
                ->hideOnForm();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Cotation_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)
            ->onlyOnForms()
            ->setColumns(12);

        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_COTATION_PISTE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('risque', PreferenceCrudController::PREF_CRM_COTATION_RISQUE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = NumberField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = AssociationField::new('monnaie', PreferenceCrudController::PREF_CRM_COTATION_MONNAIE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });

        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)
            ->setColumns(6)
            ->onlyOnForms()
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        return $tabAttributs;
    }

    public function setCRM_Fields_Cotation_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_COTATION_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_COTATION_PISTE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_RISQUE])) {
            $tabAttributs[] = AssociationField::new('risque', PreferenceCrudController::PREF_CRM_COTATION_RISQUE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE])) {
            $tabAttributs[] = NumberField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_MONNAIE])) {
            $tabAttributs[] = AssociationField::new('monnaie', PreferenceCrudController::PREF_CRM_COTATION_MONNAIE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR])) {
            $tabAttributs[] = ArrayField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_PIECES])) {
            $tabAttributs[] = ArrayField::new('pieces', PreferenceCrudController::PREF_CRM_COTATION_PIECES)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION)
                ->hideOnForm();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_FEEDBACK_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE])) {
            $tabAttributs[] = TextField::new('Message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE])) {
            $tabAttributs[] = TextField::new('prochaineTache', PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION])) {
            $tabAttributs[] = AssociationField::new('action', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET])) {
            $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)
                ->hideOnForm();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('prochaineTache', PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = AssociationField::new('action', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET)
            ->onlyOnForms()
            ->setColumns(6);
        return $tabAttributs;
    }

    public function setCRM_Fields_Etapes_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)
            ->onlyOnForms()
            ->setColumns(6);

        return $tabAttributs;
    }

    public function setCRM_Fields_Action_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_MISSION_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_NOM])) {
            $tabAttributs[] = TextField::new('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF])) {
            $tabAttributs[] = TextareaField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_STATUS])) {
            $tabAttributs[] = ChoiceField::new('clos', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
                ->hideOnForm()
                ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
                ->setChoices(ActionCRMCrudController::STATUS_MISSION);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT])) {
            $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT])) {
            $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A])) {
            $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT)
                ->hideOnForm();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Action_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = TextareaField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = ChoiceField::new('clos', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
            ->onlyOnForms()
            ->setColumns(6)
            ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
            ->setChoices(ActionCRMCrudController::STATUS_MISSION);
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)
            ->setColumns(6)
            ->onlyOnForms()
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        return $tabAttributs;
    }


    public function setCRM_Fields_Etapes_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_ETAPES_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION)
                ->hideOnForm();
        }
        return $tabAttributs;
    }




    public function setCRM_Fields_Pistes_Index_Details(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_PISTE_ID)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
                ->hideOnForm(); //->setColumns(6);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF])) {
            $tabAttributs[] = TextField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
                ->hideOnForm(); //->setColumns(6);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_MONTANT])) {
            $tabAttributs[] = NumberField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
                ->hideOnForm(); //->setColumns(6);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_CONTACT])) {
            $tabAttributs[] = CollectionField::new('contact', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_COTATION])) {
            $tabAttributs[] = CollectionField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_ACTIONS])) {
            $tabAttributs[] = CollectionField::new('actions', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_ETAPE])) {
            $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION])) {
            $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }


    private function getTitreAttributTaxe($indiceTaxe, $sufixe, $defaultLabel): string
    {
        $txtTaxe = $defaultLabel;
        $tabT = $this->getTaxes($indiceTaxe);
        if (count($tabT) == 1) {
            $txtTaxe = "Ac/" . $tabT[0]->getNom() . " (" . $tabT[0]->getTaux() * 100 . "%)/" . $sufixe;
        }
        return $txtTaxe;
    }


    private function getTitreAttributTaxe_Simple($indiceTaxe, $defaultLabel): string
    {
        $txtTaxe = $defaultLabel;
        $tabT = $this->getTaxes($indiceTaxe);
        if (count($tabT) == 1) {
            $txtTaxe = $tabT[0]->getNom() . " (" . $tabT[0]->getTaux() * 100 . "%)";
        }
        return $txtTaxe;
    }

    public function getTaxes($indiceTaxe): array
    {
        $tabTaxesResultat = [];
        switch ($indiceTaxe) {
            case self::INDICE_TAXE_ASSUREUR:
                foreach ($this->taxes as $taxe) {
                    if ($taxe->isPayableparcourtier() == false) {
                        $tabTaxesResultat[] = $taxe;
                    }
                }
                break;
            case self::INDICE_TAXE_COURTIER:
                foreach ($this->taxes as $taxe) {
                    if ($taxe->isPayableparcourtier() == true) {
                        $tabTaxesResultat[] = $taxe;
                    }
                }
                break;

            default:
                # code...
                break;
        }
        return $tabTaxesResultat;
    }

    public function setCRM_Fields_Pistes_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = NumberField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = AssociationField::new('contact', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('actions', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
            ->onlyOnForms()
            ->setColumns(6);

        //dd($tabAttributs);

        return $tabAttributs;
    }

    public function getChamps($objetInstance)
    {
        $taxes = $this->chargerTaxes();
        $preference = $this->chargerPreference($this->serviceEntreprise->getUtilisateur(), $this->serviceEntreprise->getEntreprise());
        //définition des attributs des pages
        return $this->definirAttributsPages($objetInstance, $preference);
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
