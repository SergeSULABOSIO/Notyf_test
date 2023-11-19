<?php

namespace App\Service;

use App\Entity\Taxe;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Expert;
use App\Entity\Police;
use DateTimeImmutable;
use App\Entity\Contact;
use App\Entity\Facture;
use App\Entity\Monnaie;
use App\Entity\Produit;
use App\Entity\Victime;
use App\Entity\Assureur;
use App\Entity\Cotation;
use App\Entity\DocPiece;
use App\Entity\EtapeCrm;
use App\Entity\Paiement;
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
use App\Entity\EtapeSinistre;
use App\Entity\CompteBancaire;
use App\Entity\ElementFacture;
use App\Entity\CalculableEntity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\Admin\TaxeCrudController;
use App\Controller\Admin\ClientCrudController;
use App\Controller\Admin\PoliceCrudController;
use App\Controller\Admin\FactureCrudController;
use App\Controller\Admin\MonnaieCrudController;
use App\Controller\Admin\ProduitCrudController;
use App\Controller\Admin\DocPieceCrudController;
use App\Controller\Admin\PaiementCrudController;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Controller\Admin\ActionCRMCrudController;
use App\Controller\Admin\AutomobileCrudController;
use App\Controller\Admin\ChargementCrudController;
use App\Controller\Admin\ContactCrudController;
use App\Controller\Admin\CotationCrudController;
use App\Controller\Admin\PreferenceCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use App\Controller\Admin\UtilisateurCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Controller\Admin\EtapeSinistreCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Controller\Admin\ElementFactureCrudController;
use App\Controller\Admin\PartenaireCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\TrancheCrudController;
use App\Entity\Chargement;
use App\Entity\Revenu;
use App\Entity\Tranche;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use SebastianBergmann\CodeCoverage\Util\Percentage;

class ServicePreferences
{
    private $taxes = [];
    public const INDICE_TAXE_COURTIER = 0;
    public const INDICE_TAXE_ASSUREUR = 1;

    public ?Crud $crud = null;
    public ?AdminUrlGenerator $adminUrlGenerator = null;

    public $total_unpaidcommission = 0;
    public $total_unpaidretrocommission = 0;
    public $total_unpaidtaxecourtier = 0;
    public $total_unpaidtaxeassureur = 0;
    public $total_unpaidtaxe = 0;

    public $total_paidcommission = 0;
    public $total_paidretrocommission = 0;
    public $total_paidtaxecourtier = 0;
    public $total_paidtaxeassureur = 0;
    public $total_paidtaxe = 0;

    //Production
    public $total_prime_nette = 0;
    public $total_prime_fronting = 0;
    public $total_prime_accessoire = 0;
    public $total_prime_tva = 0;
    public $total_prime_arca = 0;
    public $total_prime_ttc = 0;
    //SINISTRE
    public $total_sinistre_cout = 0;
    public $total_sinistre_indemnisation = 0;
    public $total_piste_caff_esperes = 0;


    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServiceCompteBancaire $serviceCompteBancaire,
        private ServiceTaxes $serviceTaxes,
        private ServiceMonnaie $serviceMonnaie,
        private ServiceCalculateur $serviceCalculateur
    ) {
    }

    public function chargerTaxes()
    {
        $this->taxes = $this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        );
    }

    public function chargerPreference(?Utilisateur $utilisateur, ?Entreprise $entreprise): Preference
    {
        //dd($utilisateur);
        $preferences = [];
        if ($utilisateur && $entreprise) {
            $preferences = $this->entityManager->getRepository(Preference::class)->findBy(
                [
                    'entreprise' => $entreprise,
                    'utilisateur' => $utilisateur,
                ]
            );
        }
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
        //dd($this->serviceEntreprise);
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
        if ($instance instanceof Chargement) {
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
        if ($instance instanceof CompteBancaire) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof Paiement) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof Monnaie) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof Facture) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof ElementFacture) {
            $this->setTailleFIN($preference, $crud);
        }
        if ($instance instanceof Revenu) {
            $this->setTailleFIN($preference, $crud);
        }
        //GROUPE SINISTRE
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
        //dd($tab);
        foreach ($tab as $valeur) {
            if ($valeur == $indice_attribut) {
                return true;
            }
        }
        return false;
    }

    public function canShow_url($indice_attribut)
    {
        //dd($this->adminUrlGenerator->get("champsACacher"));
        if ($this->adminUrlGenerator->get("champsACacher")) {
            foreach ($this->adminUrlGenerator->get("champsACacher") as $champsACacher) {
                if ($champsACacher == $indice_attribut) {
                    return false;
                }
            }
        }
        return true;
    }

    public function definirAttributsPages($objetInstance, Preference $preference, $crud, AdminUrlGenerator $adminUrlGenerator)
    {
        //dd($crud);
        $this->crud = $crud;
        $this->adminUrlGenerator = $adminUrlGenerator;

        //GROUPE CRM
        if ($objetInstance instanceof ActionCRM) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-paper-plane') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une mission est une ou un ensembles d'actions attribuée(s) à un ou plusieurs utilisateurs.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Action_Index_Details($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Action_Index($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Action_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Action_form($tabAttributs, $adminUrlGenerator);
        }
        if ($objetInstance instanceof FeedbackCRM) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-comments') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Un feedback est une réponse ou compte rendu attaché à une mission. Chaque mission doit avoir un ou plusieurs feedbacks.")
            ];
            $tabAttributs = $this->setCRM_Fields_Feedback_Index($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Feedback_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Feedback_form($tabAttributs);
        }
        if ($objetInstance instanceof Cotation) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-cash-register') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une cotation est tout simplement un dévis/une offre financière relative à un risque précis. Ce n'est pas une police d'assurance.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Cotation_Index_Details($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Cotation_Index($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Cotation_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Cotation_form($tabAttributs, $adminUrlGenerator);
        }
        if ($objetInstance instanceof EtapeCrm) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-list-check') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une étape (ou phase) dans le traitement d'une pistre. Le traitement d'une piste (càd sa conversion en client) est un processus qui peut passer par un certain nombre d'étapes.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Etapes_Index_Details($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Etapes_Index($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Etapes_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Etapes_form($tabAttributs);
        }
        if ($objetInstance instanceof Piste) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-location-crosshairs') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une piste est un prospect (ou client potientiel) à suivre stratégiquement afin de lui convertir en client."),
            ];
            //$tabAttributs = $this->setCRM_Fields_Pistes_Index_Details($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE, $tabAttributs, $crud, $adminUrlGenerator);
            $tabAttributs = $this->setCRM_Fields_Pistes_Index($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Pistes_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Pistes_form($tabAttributs);
        }
        //GROUPE PRODUCTION
        if ($objetInstance instanceof Assureur) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-umbrella') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le preneur des risques en contre partie du versement d'une prime d'assurance et selon les condtions bien spécifiées dans la police.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Assureur_Index_Details($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Assureur_Index($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Assureur_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Assureur_form($tabAttributs);
        }
        if ($objetInstance instanceof Automobile) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-car') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Engin auto-moteur.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Engins_Index_Details($preference->getProAutomobiles(), PreferenceCrudController::TAB_PRO_ENGINS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Engins_Index($preference->getProAutomobiles(), PreferenceCrudController::TAB_PRO_ENGINS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Engins_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Engins_form($tabAttributs);
        }
        if ($objetInstance instanceof Contact) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-address-book') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Tout simplement un contact au sens littéral du terme. Une personne à contacter dans le cadre des assurances."),
            ];
            //$tabAttributs = $this->setCRM_Fields_Contacts_Index_Details($preference->getProContacts(), PreferenceCrudController::TAB_PRO_CONTACTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Contacts_Index($preference->getProContacts(), PreferenceCrudController::TAB_PRO_CONTACTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Contacts_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Contacts_form($tabAttributs);
        }
        if ($objetInstance instanceof Client) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-person-shelter') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le client c'est l'assuré ou le bénéficiaire de la couverture d'assurance.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Clients_Index_Details($preference->getProClients(), PreferenceCrudController::TAB_PRO_CLIENTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Clients_Index($preference->getProClients(), PreferenceCrudController::TAB_PRO_CLIENTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Clients_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Clients_form($tabAttributs);
        }
        if ($objetInstance instanceof Partenaire) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-handshake') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le partenaire ou intermédiaire à travers lequel un client peut être acquis.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Partenaires_Index_Details($preference->getProPartenaires(), PreferenceCrudController::TAB_PRO_PARTENAIRES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Partenaires_Index($preference->getProPartenaires(), PreferenceCrudController::TAB_PRO_PARTENAIRES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Partenaires_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Partenaires_form($tabAttributs);
        }
        if ($objetInstance instanceof Police) {
            $tabAttributs = [
                FormField::addTab(' Informations de base')
                    ->setIcon('fas fa-file-shield') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le contrat d'assurance en place.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Polices_Index_Details($preference->getProPolices(), PreferenceCrudController::TAB_PRO_POLICES, $tabAttributs, $crud, $adminUrlGenerator);
            $tabAttributs = $this->setCRM_Fields_Polices_Index($preference->getProPolices(), PreferenceCrudController::TAB_PRO_POLICES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Polices_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Polices_form($tabAttributs);
        }
        if ($objetInstance instanceof Produit) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-gifts') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une couverture d'assurance.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Produits_Index_Details($preference->getProProduits(), PreferenceCrudController::TAB_PRO_PRODUITS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Produits_Index($preference->getProProduits(), PreferenceCrudController::TAB_PRO_PRODUITS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Produits_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Produits_form($tabAttributs);
        }
        //GROUPE FINANCES
        if ($objetInstance instanceof Taxe) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-landmark-dome') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Taxes ou Impôts dûes aux autorités étatiques.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Taxes_Index_Details($preference->getFinTaxes(), PreferenceCrudController::TAB_FIN_TAXES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Taxes_Index($preference->getFinTaxes(), PreferenceCrudController::TAB_FIN_TAXES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Taxes_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Taxes_form($tabAttributs);
        }
        if ($objetInstance instanceof Monnaie) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-money-bill-1') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Monnaie de change.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Monnaies_Index_Details($preference->getFinMonnaies(), PreferenceCrudController::TAB_FIN_MONNAIES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Monnaies_Index($preference->getFinMonnaies(), PreferenceCrudController::TAB_FIN_MONNAIES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Monnaies_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Monnaies_form($tabAttributs);
        }
        if ($objetInstance instanceof Facture) {
            $tabAttributs = [
                FormField::addPanel('Facture')
                    ->setIcon('fa-solid fa-receipt') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Facture / Note de débit.")
            ];
            $tabAttributs = $this->setFIN_Fields_Facture_Index($preference->getFinFactures(), PreferenceCrudController::TAB_FIN_FACTURE, $tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Facture_Details($tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Facture_form($tabAttributs);
        }
        if ($objetInstance instanceof CompteBancaire) {
            $tabAttributs = [
                FormField::addPanel('Compte Bancaire')
                    ->setIcon('fa-solid fa-piggy-bank') //<i class="fa-solid fa-piggy-bank"></i>
                    ->setHelp("Votre compte bancaire tout simplement.")
            ];
            $tabAttributs = $this->setFIN_Fields_CompteBancaire_Index($preference->getFinCompteBancaires(), PreferenceCrudController::TAB_FIN_COMPTE_BANCAIRE, $tabAttributs);
            $tabAttributs = $this->setFIN_Fields_CompteBancaire_Details($tabAttributs);
            $tabAttributs = $this->setFIN_Fields_CompteBancaire_form($tabAttributs);
        }
        if ($objetInstance instanceof Revenu) {
            $tabAttributs = [
                FormField::addPanel('Revenu')
                    ->setIcon('fa-solid fa-burger') //<i class="fa-solid fa-burger"></i>
                    ->setHelp("Votre revenu.")
            ];
            $tabAttributs = $this->setFIN_Fields_Revenu_Index($preference->getFinRevenu(), PreferenceCrudController::TAB_FIN_REVENU, $tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Revenu_Details($tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Revenu_form($tabAttributs);
        }
        if ($objetInstance instanceof Chargement) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fa-solid fa-comment-dollar') //<i class="fa-solid fa-comment-dollar"></i>
                    ->setHelp("Frais chargés au client par l'assureur, visibles sur la facture.")
            ];
            //$tabAttributs = $this->setCRM_Fields_Monnaies_Index_Details($preference->getFinMonnaies(), PreferenceCrudController::TAB_FIN_MONNAIES, $tabAttributs);
            $tabAttributs = $this->setPROD_Fields_Chargement_Index($preference->getProdChargement(), PreferenceCrudController::TAB_PROD_CHARGEMENT, $tabAttributs);
            $tabAttributs = $this->setPROD_Fields_Chargement_Details($tabAttributs);
            $tabAttributs = $this->setPROD_Fields_Chargement_form($tabAttributs);
        }
        if ($objetInstance instanceof Tranche) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fa-solid fa-layer-group') //<i class="fa-solid fa-layer-group"></i>
                    ->setHelp("Portion de la prime totale valide et payable pour une période bien déterminée conformément aux termes de paiement.")
            ];
            $tabAttributs = $this->setPROD_Fields_Tranche_Index($preference->getProTranches(), PreferenceCrudController::TAB_PROD_TRANCHE, $tabAttributs);
            $tabAttributs = $this->setPROD_Fields_Tranche_Details($tabAttributs);
            $tabAttributs = $this->setPROD_Fields_Tranche_form($tabAttributs);
        }
        if ($objetInstance instanceof ElementFacture) {

            //dd($adminUrlGenerator->get("donnees"));
            $tabAttributs = [
                FormField::addPanel('Elément facture')
                    ->setIcon('fa-solid fa-cart-plus') //<i class="fa-solid fa-cart-plus"></i>
                    ->setHelp("Elément à inclure dans la facture.")
            ];

            $tabAttributs = $this->setFIN_Fields_Element_Facture_Index($preference->getFinFactures(), PreferenceCrudController::TAB_FIN_ELEMENT_FACTURE, $tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Element_Facture_Details($tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Element_Facture_form($tabAttributs);
        }
        if ($objetInstance instanceof Paiement) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fa-solid fa-cash-register') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Cashflow.")
            ];
            //$tabAttributs = $this->setCRM_Fields_PaiementTaxes_Index_Details($preference->getFinTaxesPayees(), PreferenceCrudController::TAB_FIN_PAIEMENTS_TAXES, $tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Paiement_Index($preference->getFinTaxesPayees(), PreferenceCrudController::TAB_FIN_PAIEMENT, $tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Paiement_Details($tabAttributs);
            $tabAttributs = $this->setFIN_Fields_Paiement_form($tabAttributs);
        }
        //GROUPE SINISTRE
        if ($objetInstance instanceof EtapeSinistre) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-arrow-down-short-wide') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le traitement d'un sinistre passe par une ou plusieurs étapes. De la déclaration à l'indemnisation.")
            ];
            //$tabAttributs = $this->setCRM_Fields_EtapeSinistres_Index_Details($preference->getSinEtapes(), PreferenceCrudController::TAB_SIN_ETAPES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_EtapeSinistres_Index($preference->getSinEtapes(), PreferenceCrudController::TAB_SIN_ETAPES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_EtapeSinistres_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_EtapeSinistres_form($tabAttributs);
        }
        if ($objetInstance instanceof Expert) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-user-graduate') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("L'expert est une personne morale ou physique qui a pour rôle d'aider l'assureur à mieux évaluer l'ampleur du dégât (évaluation chiffrée) afin de déterminer le montant réel de la compensation."),
            ];
            //$tabAttributs = $this->setCRM_Fields_ExpertSinistres_Index_Details($preference->getSinExperts(), PreferenceCrudController::TAB_SIN_EXPERTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_ExpertSinistres_Index($preference->getSinExperts(), PreferenceCrudController::TAB_SIN_EXPERTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_ExpertSinistres_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_ExpertSinistres_form($tabAttributs);
        }
        if ($objetInstance instanceof Sinistre) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-bell') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Evènement(s) malheureux pouvant déclancher le processus d'indemnisation selon les termes de la police."),
            ];
            //$tabAttributs = $this->setCRM_Fields_SinistreSinistres_Index_Details($preference->getSinSinistres(), PreferenceCrudController::TAB_SIN_SINISTRES, $tabAttributs, $crud, $adminUrlGenerator);
            $tabAttributs = $this->setCRM_Fields_SinistreSinistres_Index($preference->getSinSinistres(), PreferenceCrudController::TAB_SIN_SINISTRES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_SinistreSinistres_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_SinistreSinistres_form($tabAttributs);
        }
        if ($objetInstance instanceof Victime) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-person-falling-burst') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Personne (morale ou physique) laisée ou ayant subi les dommages au cours du sinistre."),
            ];
            //$tabAttributs = $this->setCRM_Fields_SinistreVictimes_Index_Details($preference->getSinVictimes(), PreferenceCrudController::TAB_SIN_VICTIMES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_SinistreVictimes_Index($preference->getSinVictimes(), PreferenceCrudController::TAB_SIN_VICTIMES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_SinistreVictimes_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_SinistreVictimes_form($tabAttributs);
        }
        //GROUPE BIBLIOTHEQUE
        if ($objetInstance instanceof DocCategorie) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-tags') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Tout simplement un ensemble des documents qui partagent un certain nombre des critères communs."),
            ];
            //$tabAttributs = $this->setCRM_Fields_BibliothequeCategories_Index_Details($preference->getBibCategories(), PreferenceCrudController::TAB_BIB_CATEGORIES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequeCategories_Index($preference->getBibCategories(), PreferenceCrudController::TAB_BIB_CATEGORIES, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequeCategories_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequeCategories_form($tabAttributs);
        }
        if ($objetInstance instanceof DocClasseur) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-folder-open') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Un classeur représente un dossier (virtuel) dans lequel peuvent se ranger un ou plusieurs documents."),
            ];
            //$tabAttributs = $this->setCRM_Fields_BibliothequeClasseurs_Index_Details($preference->getBibClasseurs(), PreferenceCrudController::TAB_BIB_CLASSEURS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequeClasseurs_Index($preference->getBibClasseurs(), PreferenceCrudController::TAB_BIB_CLASSEURS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequeClasseurs_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequeClasseurs_form($tabAttributs);
        }
        if ($objetInstance instanceof DocPiece) {
            $tabAttributs = [
                FormField::addTab('Informations générales')
                    ->setIcon('fas fa-file-word') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une pièce est un document de quel que format que ce soit."),
            ];
            //$tabAttributs = $this->setCRM_Fields_BibliothequePieces_Index_Details($preference->getBibPieces(), PreferenceCrudController::TAB_BIB_DOCUMENTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequePieces_Index($preference->getBibPieces(), PreferenceCrudController::TAB_BIB_DOCUMENTS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequePieces_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_BibliothequePieces_form($tabAttributs);
        }
        //GROUPE PARAMETRES
        if ($objetInstance instanceof Utilisateur) {
            $tabAttributs = [
                FormField::addPanel(' Profil')
                    ->setIcon('fas fa-user') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("L'utilisateur ayant un certain droit d'accès aux données et pouvant utiliser le système."),
            ];
            //$tabAttributs = $this->setCRM_Fields_ParUtilisateurs_Index_Details($preference->getParUtilisateurs(), PreferenceCrudController::TAB_PAR_UTILISATEURS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_ParUtilisateurs_Index($preference->getParUtilisateurs(), PreferenceCrudController::TAB_PAR_UTILISATEURS, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_ParUtilisateurs_Details($tabAttributs);
            $tabAttributs = $this->setCRM_Fields_ParUtilisateurs_form($tabAttributs);
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Polices_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_POLICE_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)->onlyOnDetail();
        //$tabAttributs[] = ArrayField::new('factures', PreferenceCrudController::PREF_PRO_POLICE_FACTURES)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)->onlyOnDetail();
        $tabAttributs[] = NumberField::new('idavenant', PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
            ->onlyOnDetail()
            ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT);
        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_PRO_POLICE_CLIENT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('actionCRMs', PreferenceCrudController::PREF_PRO_POLICE_ACTIONS)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('pistes', PreferenceCrudController::PREF_PRO_POLICE_PISTES)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('sinistres', PreferenceCrudController::PREF_PRO_POLICE_SINISTRES)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('automobiles', PreferenceCrudController::PREF_PRO_POLICE_AUTOMOBILES)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('docPieces', PreferenceCrudController::PREF_PRO_POLICE_PIECES)->onlyOnDetail();
        $tabAttributs[] = TextField::new('reassureurs', PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)->onlyOnDetail();
        $tabAttributs[] = TextareaField::new('remarques', PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_POLICE_ENTREPRISE)->onlyOnDetail();

        //Onglet Prime & Capitaux
        $tabAttributs[] = FormField::addTab(' Prime & Capitaux')
            ->setIcon('fas fa-bag-shopping')
            ->setHelp("Le contrat d'assurance en place.")
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('capital', PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getCapital());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('modepaiement', PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            ->onlyOnDetail()
            ->setChoices(PoliceCrudController::TAB_POLICE_MODE_PAIEMENT);
        $tabAttributs[] = FormField::addPanel('Facture client')->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('primenette', PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimenette());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('fronting', PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFronting());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('arca', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_COURTIER, PreferenceCrudController::PREF_PRO_POLICE_ARCA))
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getArca());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('tva', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_ASSUREUR, PreferenceCrudController::PREF_PRO_POLICE_TVA))
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTva());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('fraisadmin', PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFraisadmin());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('discount', PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getDiscount());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('primetotale', PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimetotale());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();

        //Onglet Structure des revenus
        $tabAttributs[] = FormField::addTab(' Structure des revenus')->setIcon('fas fa-sack-dollar')->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)->onlyOnDetail();
        $tabAttributs[] = PercentField::new('partExceptionnellePartenaire', PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)->onlyOnDetail();
        $tabAttributs[] = FormField::addPanel('Commission de réassurance')->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('ricom', PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRicom());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('cansharericom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
            ->onlyOnDetail()
            ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON);
        $tabAttributs[] = ChoiceField::new('ricompayableby', PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
            ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
            ->onlyOnDetail();
        $tabAttributs[] = FormField::addPanel("Commission locale")->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('localcom', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getLocalcom());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('cansharelocalcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
            ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('localcompayableby', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
            ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
            ->onlyOnDetail();
        $tabAttributs[] = FormField::addPanel("Commission sur Fronting")->hideOnForm();
        $tabAttributs[] = MoneyField::new('frontingcom', PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFrontingcom());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('cansharefrontingcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
            ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('frontingcompayableby', PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
            ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
            ->onlyOnDetail();

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_details(true, $tabAttributs);

        //TRAVAUX SUR LE REPORTING
        $tabAttributs[] = MoneyField::new('unpaidcommission')
            ->formatValue(function ($value, Police $police) {
                $this->setTitreReporting($police);
                return $value;
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setFIN_Fields_Revenu_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_REVENU_ID)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_REVENU_TYPE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_TYPE);
        $tabAttributs[] = ChoiceField::new('partageable', PreferenceCrudController::PREF_FIN_REVENU_PARTAGEABLE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_PARTAGEABLE);
        $tabAttributs[] = ChoiceField::new('taxable', PreferenceCrudController::PREF_FIN_REVENU_TAXABLE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_TAXABLE);
        $tabAttributs[] = ChoiceField::new('base', PreferenceCrudController::PREF_FIN_REVENU_BASE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_BASE);
        $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_FIN_REVENU_TAUX)->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_REVENU_MONTANT_FLAT)
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_REVENU_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_REVENU__DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_REVENU_DERNIRE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_REVENU_ENTREPRISE)->onlyOnDetail();
        return $tabAttributs;
    }

    public function setPROD_Fields_Chargement_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PROD_CHARGEMENT_ID)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_PROD_CHARGEMENT_TYPE)
            ->onlyOnDetail()
            ->setChoices(ChargementCrudController::TAB_TYPE);
        $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_PROD_CHARGEMENT_DESCRIPTION)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_PROD_CHARGEMENT_MONTANT)
            ->formatValue(function ($value, Chargement $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PROD_CHARGEMENT_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PROD_CHARGEMENT_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PROD_CHARGEMENT_DERNIRE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PROD_CHARGEMENT_ENTREPRISE)->onlyOnDetail();
        return $tabAttributs;
    }

    public function setPROD_Fields_Tranche_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PROD_TRANCHE_ID)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PROD_TRANCHE_COTATION)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PROD_TRANCHE_NOM)->onlyOnDetail();
        $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_PROD_TRANCHE_TAUX)->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_PROD_TRANCHE_MONTANT)
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_PROD_TRANCHE_DEBUT)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_PROD_TRANCHE_FIN)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PROD_TRANCHE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PROD_TRANCHE_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PROD_TRANCHE_DERNIRE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PROD_TRANCHE_ENTREPRISE)->onlyOnDetail();
        return $tabAttributs;
    }


    public function setCRM_Fields_Polices_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_POLICE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_REFERENCE])) {
            $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION])) {
            $tabAttributs[] = DateTimeField::new('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION])) {
            $tabAttributs[] = DateTimeField::new('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET])) {
            $tabAttributs[] = DateTimeField::new('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION])) {
            $tabAttributs[] = DateTimeField::new('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE])) {
            $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT])) {
            $tabAttributs[] = NumberField::new('idavenant', PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT])) {
            $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
                ->onlyOnIndex()
                ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT)
                ->renderAsBadges([
                    // $value => $badgeStyleName
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_ANNULATION] => 'dark', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_SOUSCRIPTION] => 'success', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_INCORPORATION] => 'info', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_PROROGATION] => 'success', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RENOUVELLEMENT] => 'success', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RESILIATION] => 'warning', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RISTOURNE] => 'danger', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION] => 'info', //info
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE])) {
            $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CLIENT])) {
            $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_PRO_POLICE_CLIENT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PRODUIT])) {
            $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_COTATION])) {
            $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS])) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ACTIONS])) {
            $tabAttributs[] = AssociationField::new('actionCRMs', PreferenceCrudController::PREF_PRO_POLICE_ACTIONS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PISTES])) {
            $tabAttributs[] = AssociationField::new('pistes', PreferenceCrudController::PREF_PRO_POLICE_PISTES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_SINISTRES])) {
            $tabAttributs[] = AssociationField::new('sinistres', PreferenceCrudController::PREF_PRO_POLICE_SINISTRES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_AUTOMOBILES])) {
            $tabAttributs[] = AssociationField::new('automobiles', PreferenceCrudController::PREF_PRO_POLICE_AUTOMOBILES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PIECES])) {
            $tabAttributs[] = AssociationField::new('docPieces', PreferenceCrudController::PREF_PRO_POLICE_PIECES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS])) {
            $tabAttributs[] = TextField::new('reassureurs', PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
                ->onlyOnIndex();
        }

        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_REMARQUE])) {
            $tabAttributs[] = TextareaField::new('remarques', PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_POLICE_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CAPITAL])) {
            $tabAttributs[] = MoneyField::new('capital', PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getCapital());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT])) {
            $tabAttributs[] = ChoiceField::new('modepaiement', PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
                ->onlyOnIndex()
                ->setChoices(PoliceCrudController::TAB_POLICE_MODE_PAIEMENT);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE])) {
            $tabAttributs[] = MoneyField::new('primenette', PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimenette());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRONTING])) {
            $tabAttributs[] = MoneyField::new('fronting', PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFronting());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_ARCA])) {
            $tabAttributs[] = MoneyField::new('arca', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_COURTIER, PreferenceCrudController::PREF_PRO_POLICE_ARCA))
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getArca());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_TVA])) {
            $tabAttributs[] = MoneyField::new('tva', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_ASSUREUR, PreferenceCrudController::PREF_PRO_POLICE_TVA))
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTva());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN])) {
            $tabAttributs[] = MoneyField::new('fraisadmin', PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFraisadmin());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT])) {
            $tabAttributs[] = MoneyField::new('discount', PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getDiscount());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE])) {
            $tabAttributs[] = MoneyField::new('primetotale', PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimetotale());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE])) {
            $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE])) {
            $tabAttributs[] = PercentField::new('partExceptionnellePartenaire', PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_RI_COM])) {
            $tabAttributs[] = MoneyField::new('ricom', PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRicom());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM])) {
            $tabAttributs[] = ChoiceField::new('cansharericom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_RI_COM)
                ->onlyOnIndex()
                ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY])) {
            $tabAttributs[] = ChoiceField::new('ricompayableby', PreferenceCrudController::PREF_PRO_POLICE_RI_COM_PAYABLE_BY)
                ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM])) {
            $tabAttributs[] = MoneyField::new('localcom', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getLocalcom());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM])) {
            $tabAttributs[] = ChoiceField::new('cansharelocalcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_LOCAL_COM)
                ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY])) {
            $tabAttributs[] = ChoiceField::new('localcompayableby', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY)
                ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM])) {
            $tabAttributs[] = MoneyField::new('frontingcom', PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM)
                ->formatValue(function ($value, Police $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFrontingcom());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM])) {
            $tabAttributs[] = ChoiceField::new('cansharefrontingcom', PreferenceCrudController::PREF_PRO_POLICE_CANHSARE_FRONTING_COM)
                ->setChoices(PoliceCrudController::TAB_POLICE_REPONSES_OUI_NON)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY])) {
            $tabAttributs[] = ChoiceField::new('frontingcompayableby', PreferenceCrudController::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY)
                ->setChoices(PoliceCrudController::TAB_POLICE_DEBITEUR)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_Index(true, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        //TRAVAUX SUR LE REPORTING
        $tabAttributs[] = MoneyField::new('unpaidcommission')
            ->formatValue(function ($value, Police $police) {
                $this->setTitreReporting($police);
                return $value;
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();

        return $tabAttributs;
    }

    public function setFIN_Fields_Facture_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_FACTURE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_STATUS])) {
            $tabAttributs[] = ChoiceField::new('status', PreferenceCrudController::PREF_FIN_FACTURE_STATUS)
                ->onlyOnIndex()
                ->setChoices(FactureCrudController::TAB_STATUS_FACTURE)
                ->renderAsBadges([
                    FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_SOLDEE] => 'success', //info
                    FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE] => 'danger', //info
                    FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_ENCOURS] => 'info', //info
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE])) {
            $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_TYPE])) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->onlyOnIndex()
                ->setChoices(FactureCrudController::TAB_TYPE_FACTURE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS])) {
            $tabAttributs[] = AssociationField::new('elementFactures', PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)
                ->formatValue(function ($value, Facture $entity) {
                    return count($entity->getElementFactures()) == 0 ? "Aucun élément" : count($entity->getElementFactures()) . " élement(s).";
                })
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_DU])) {
            $tabAttributs[] = MoneyField::new('totalDu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_DU)
                ->formatValue(function ($value, Facture $facture) {
                    $this->setTotauxFacture($facture);
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($facture->getTotalDu());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_RECU])) {
            $tabAttributs[] = MoneyField::new('totalRecu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_RECU)
                ->formatValue(function ($value, Facture $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalRecu());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_SOLDE])) {
            $tabAttributs[] = MoneyField::new('totalSolde', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_SOLDE)
                ->formatValue(function ($value, Facture $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalSolde());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE])) {
            $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR])) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS])) {
            $tabAttributs[] = TextField::new('autreTiers', PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY])) {
            $tabAttributs[] = TextField::new('signedBy', PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY])) {
            $tabAttributs[] = TextField::new('posteSignedBy', PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES])) {
            $tabAttributs[] = AssociationField::new('compteBancaires', PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
                ->formatValue(function ($value, Facture $entity) {
                    return count($entity->getCompteBancaires()) == 0 ? "Aucun Compte Bancaire" : count($entity->getCompteBancaires()) . " compte(s) bancaire(s).";
                })
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_PIECE])) {
            $tabAttributs[] = AssociationField::new('piece', PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_FACTURE_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }


    public function setFIN_Fields_Paiement_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_PAIEMENT_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE])) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE)
                ->onlyOnIndex()
                ->setChoices(PaiementCrudController::TAB_TYPE_PAIEMENT)
                ->renderAsBadges([
                    PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE] => 'success', //info
                    PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE] => 'danger', //info
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_MONTANT])) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_PAIEMENT_MONTANT)
                ->formatValue(function ($value, Paiement $paiement) {
                    $this->setTotauxPaiement($paiement);
                    if ($paiement->getType() == PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE]) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($paiement->getMontant());
                    } else {
                        return '- ' . $this->serviceMonnaie->getMonantEnMonnaieAffichage($paiement->getMontant());
                    }
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_DATE])) {
            $tabAttributs[] = DateTimeField::new('paidAt', PreferenceCrudController::PREF_FIN_PAIEMENT_DATE)
                ->onlyOnIndex();
        }
        /* if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_DESCRIPTION])) {
            $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_FIN_PAIEMENT_DESCRIPTION)
                ->onlyOnIndex();
        } */
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_FACTURE])) {
            $tabAttributs[] = AssociationField::new('facture', PreferenceCrudController::PREF_FIN_PAIEMENT_FACTURE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_PIECE])) {
            $tabAttributs[] = CollectionField::new('pieces', PreferenceCrudController::PREF_FIN_PAIEMENT_PIECE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_COMPTE_BANCAIRE])) {
            $tabAttributs[] = AssociationField::new('compteBancaire', PreferenceCrudController::PREF_FIN_PAIEMENT_COMPTE_BANCAIRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_PAIEMENT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_PAIEMENT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_PAIEMENT_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setFIN_Fields_Paiement_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_PAIEMENT_ID)->onlyOnDetail();
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE)) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE)
                ->setChoices(PaiementCrudController::TAB_TYPE_PAIEMENT)
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_DATE)) {
            $tabAttributs[] = DateTimeField::new('paidAt', PreferenceCrudController::PREF_FIN_PAIEMENT_DATE)
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_MONTANT)) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_PAIEMENT_MONTANT)
                ->formatValue(function ($value, Paiement $paiement) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($paiement->getMontant());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_DESCRIPTION)) {
            $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_FIN_PAIEMENT_DESCRIPTION)
                ->renderAsHtml()
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_FACTURE)) {
            $tabAttributs[] = AssociationField::new('facture', PreferenceCrudController::PREF_FIN_PAIEMENT_FACTURE)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_PIECE)) {
            $tabAttributs[] = CollectionField::new('pieces', PreferenceCrudController::PREF_FIN_PAIEMENT_PIECE)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_COMPTE_BANCAIRE)) {
            $tabAttributs[] = AssociationField::new('compteBancaire', PreferenceCrudController::PREF_FIN_PAIEMENT_COMPTE_BANCAIRE)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_CREATION)) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_CREATION)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_MODIFICATION)) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_PAIEMENT_DATE_MODIFICATION)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_UTILISATEUR)) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_PAIEMENT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_ENTREPRISE)) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_PAIEMENT_ENTREPRISE)->onlyOnDetail();
        }
        return $tabAttributs;
    }

    public function setFIN_Fields_Paiement_form($tabAttributs)
    {
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE)) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_PAIEMENT_TYPE)
                ->setChoices(PaiementCrudController::TAB_TYPE_PAIEMENT)
                ->setColumns(2)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_FACTURE)) {
            $tabAttributs[] = AssociationField::new('facture', PreferenceCrudController::PREF_FIN_PAIEMENT_FACTURE)
                ->setRequired(false)
                ->setColumns(10)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_MONTANT)) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_PAIEMENT_MONTANT)
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setColumns(2);
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_COMPTE_BANCAIRE)) {
            $tabAttributs[] = AssociationField::new('compteBancaire', PreferenceCrudController::PREF_FIN_PAIEMENT_COMPTE_BANCAIRE)
                ->setRequired(false)
                ->setColumns(6)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_DATE)) {
            $tabAttributs[] = DateTimeField::new('paidAt', PreferenceCrudController::PREF_FIN_PAIEMENT_DATE)
                ->setColumns(4)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_DESCRIPTION)) {
            $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_FIN_PAIEMENT_DESCRIPTION)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_PAIEMENT_PIECE)) {
            $tabAttributs[] = CollectionField::new('pieces', PreferenceCrudController::PREF_FIN_PAIEMENT_PIECE)
                ->useEntryCrudForm(DocPieceCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setEntryIsComplex()
                ->setRequired(false)
                ->setColumns(6)
                ->onlyOnForms();
        }
        //On désactive les champs non éditables
        $this->appliquerCanDesable($tabAttributs);
        return $tabAttributs;
    }

    public function setFIN_Fields_Revenu_form($tabAttributs)
    {
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_TYPE)) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_REVENU_TYPE)
                ->setChoices(RevenuCrudController::TAB_TYPE)
                ->setColumns(12)
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_PARTAGEABLE)) {
            $tabAttributs[] = ChoiceField::new('partageable', PreferenceCrudController::PREF_FIN_REVENU_PARTAGEABLE)
                ->setChoices(RevenuCrudController::TAB_PARTAGEABLE)
                ->setColumns(12)
                ->renderExpanded()
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_TAXABLE)) {
            $tabAttributs[] = ChoiceField::new('taxable', PreferenceCrudController::PREF_FIN_REVENU_TAXABLE)
                ->setChoices(RevenuCrudController::TAB_TAXABLE)
                ->setColumns(12)
                ->renderExpanded()
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_PAR_TRANCHE)) {
            $tabAttributs[] = BooleanField::new('isparttranche', PreferenceCrudController::PREF_FIN_REVENU_PAR_TRANCHE)
                ->setColumns(12)
                ->renderAsSwitch(true) //il reste éditable
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_PAR_CLIENT)) {
            $tabAttributs[] = BooleanField::new('ispartclient', PreferenceCrudController::PREF_FIN_REVENU_PAR_CLIENT)
                ->setColumns(12)
                ->renderAsSwitch(true) //il reste éditable
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_BASE)) {
            $tabAttributs[] = ChoiceField::new('base', PreferenceCrudController::PREF_FIN_REVENU_BASE)
                ->setChoices(RevenuCrudController::TAB_BASE)
                ->setColumns(12)
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_TAUX)) {
            $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_FIN_REVENU_TAUX)
                ->setColumns(12)
                ->setNumDecimals(2)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_REVENU_MONTANT_FLAT)) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_REVENU_MONTANT_FLAT)
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setStoredAsCents()
                ->setColumns(12)
                ->onlyOnForms();
        }
        //On désactive les champs non éditables
        $this->appliquerCanDesable($tabAttributs);
        return $tabAttributs;
    }

    public function setPROD_Fields_Chargement_form($tabAttributs)
    {
        if ($this->canShow_url(PreferenceCrudController::PREF_PROD_CHARGEMENT_TYPE)) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_PROD_CHARGEMENT_TYPE)
                ->setChoices(ChargementCrudController::TAB_TYPE)
                ->setColumns(12)
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_PROD_CHARGEMENT_MONTANT)) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_PROD_CHARGEMENT_MONTANT)
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setStoredAsCents()
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_PROD_CHARGEMENT_DESCRIPTION)) {
            $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_PROD_CHARGEMENT_DESCRIPTION)
                ->setColumns(12)
                ->onlyOnForms();
        }
        //On désactive les champs non éditables
        $this->appliquerCanDesable($tabAttributs);
        return $tabAttributs;
    }

    public function setPROD_Fields_Tranche_form($tabAttributs)
    {
        if ($this->canShow_url(PreferenceCrudController::PREF_PROD_TRANCHE_NOM)) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PROD_TRANCHE_NOM)
                ->setColumns(12)
                ->setRequired(true)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_PROD_TRANCHE_TAUX)) {
            $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_PROD_TRANCHE_TAUX)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_PROD_TRANCHE_DUREE)) {
            $tabAttributs[] = NumberField::new('duree', PreferenceCrudController::PREF_PROD_TRANCHE_DUREE)
                ->setColumns(12)
                ->onlyOnForms();
        }
        // if ($this->canShow_url(PreferenceCrudController::PREF_PROD_TRANCHE_DEBUT)) {
        //     $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_PROD_TRANCHE_DEBUT)
        //         ->setColumns(12)
        //         ->onlyOnForms();
        // }
        // if ($this->canShow_url(PreferenceCrudController::PREF_PROD_TRANCHE_FIN)) {
        //     $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_PROD_TRANCHE_FIN)
        //         ->setColumns(12)
        //         ->onlyOnForms();
        // }
        //On désactive les champs non éditables
        $this->appliquerCanDesable($tabAttributs);
        return $tabAttributs;
    }

    public function setFIN_Fields_CompteBancaire_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_INTITULE])) {
            $tabAttributs[] = TextField::new('intitule', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_INTITULE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_NUMERO])) {
            $tabAttributs[] = TextField::new('numero', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_NUMERO)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_BANQUE])) {
            $tabAttributs[] = TextField::new('banque', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_BANQUE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_CODESWIFT])) {
            $tabAttributs[] = TextField::new('codeSwift', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_CODESWIFT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_MONNAIE])) {
            $tabAttributs[] = TextField::new('codeMonnaie', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_MONNAIE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_DATE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setFIN_Fields_Revenu_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_REVENU_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_TYPE])) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_REVENU_TYPE)
                ->onlyOnIndex()
                ->setChoices(RevenuCrudController::TAB_TYPE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_PARTAGEABLE])) {
            $tabAttributs[] = ChoiceField::new('partageable', PreferenceCrudController::PREF_FIN_REVENU_PARTAGEABLE)
                ->onlyOnIndex()
                ->renderExpanded()
                ->setChoices(RevenuCrudController::TAB_PARTAGEABLE)
                ->renderAsBadges([
                    RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON] => 'dark',
                    RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI] => 'success',
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_TAXABLE])) {
            $tabAttributs[] = ChoiceField::new('taxable', PreferenceCrudController::PREF_FIN_REVENU_TAXABLE)
                ->onlyOnIndex()
                ->renderExpanded()
                ->setChoices(RevenuCrudController::TAB_TAXABLE)
                ->renderAsBadges([
                    RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'danger',
                    RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'success',
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_BASE])) {
            $tabAttributs[] = ChoiceField::new('base', PreferenceCrudController::PREF_FIN_REVENU_BASE)
                ->onlyOnIndex()
                ->setChoices(RevenuCrudController::TAB_BASE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_TAUX])) {
            $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_FIN_REVENU_TAUX)
                ->setNumDecimals(2)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_MONTANT_FLAT])) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_REVENU_MONTANT_FLAT)
                ->formatValue(function ($value, Revenu $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU__DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_REVENU__DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_REVENU_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_REVENU_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_REVENU_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_REVENU_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setPROD_Fields_Chargement_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PROD_CHARGEMENT_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_TYPE])) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_PROD_CHARGEMENT_TYPE)
                ->onlyOnIndex()
                ->setChoices(ChargementCrudController::TAB_TYPE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_DESCRIPTION])) {
            $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_PROD_CHARGEMENT_DESCRIPTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_MONTANT])) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_PROD_CHARGEMENT_MONTANT)
                ->formatValue(function ($value, Chargement $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PROD_CHARGEMENT_DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PROD_CHARGEMENT_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PROD_CHARGEMENT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_CHARGEMENT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PROD_CHARGEMENT_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setPROD_Fields_Tranche_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PROD_TRANCHE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_COTATION])) {
            $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PROD_TRANCHE_COTATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PROD_TRANCHE_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_TAUX])) {
            $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_PROD_TRANCHE_TAUX)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_MONTANT])) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_PROD_TRANCHE_MONTANT)
                ->formatValue(function ($value, Tranche $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_DEBUT])) {
            $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_PROD_TRANCHE_DEBUT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_FIN])) {
            $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_PROD_TRANCHE_FIN)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PROD_TRANCHE_DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PROD_TRANCHE_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PROD_TRANCHE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PROD_TRANCHE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PROD_TRANCHE_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setFIN_Fields_Element_Facture_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_POLICE])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_POLICE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_FACTURE])) {
            $tabAttributs[] = AssociationField::new('facture', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_FACTURE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_MONTANT])) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_MONTANT)
                ->formatValue(function ($value, ElementFacture $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setFIN_Fields_Element_Facture_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_ID)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_POLICE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('facture', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_FACTURE)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_MONTANT)
            ->formatValue(function ($value, ElementFacture $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_ENTREPRISE)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setFIN_Fields_Element_Facture_form($tabAttributs)
    {
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_MONTANT)
            ->formatValue(function ($value, ElementFacture $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_POLICE)
            ->setColumns(12)
            ->setRequired(false)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setFIN_Fields_CompteBancaire_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('intitule', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_INTITULE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('numero', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_NUMERO)->onlyOnDetail();
        $tabAttributs[] = TextField::new('banque', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_BANQUE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('codeSwift', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_CODESWIFT)->onlyOnDetail();
        $tabAttributs[] = TextField::new('codeMonnaie', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_MONNAIE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)->onlyOnDetail();
        return $tabAttributs;
    }


    public function setFIN_Fields_Facture_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_FACTURE_ID)->onlyOnDetail();
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_TYPE)) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->setChoices(FactureCrudController::TAB_TYPE_FACTURE)
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)) {
            $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)) {
            $tabAttributs[] = ArrayField::new('elementFactures', PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_DU)) {
            $tabAttributs[] = MoneyField::new('totalDu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_DU)
                ->formatValue(function ($value, Facture $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalDu());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_RECU)) {
            $tabAttributs[] = MoneyField::new('totalRecu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_RECU)
                ->formatValue(function ($value, Facture $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalRecu());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_SOLDE)) {
            $tabAttributs[] = MoneyField::new('totalSolde', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_SOLDE)
                ->formatValue(function ($value, Facture $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalSolde());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_PAIEMENTS)) {
            $tabAttributs[] = ArrayField::new('paiements', PreferenceCrudController::PREF_FIN_FACTURE_PAIEMENTS)
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_DESCRIPTION)) {
            $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_FIN_FACTURE_DESCRIPTION)
                ->renderAsHtml()
                ->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY)) {
            $tabAttributs[] = TextField::new('signedBy', PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY)) {
            $tabAttributs[] = TextField::new('posteSignedBy', PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)) {
            $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)) {
            $tabAttributs[] = TextField::new('autreTiers', PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_PIECE)) {
            $tabAttributs[] = AssociationField::new('piece', PreferenceCrudController::PREF_FIN_FACTURE_PIECE)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)) {
            $tabAttributs[] = ArrayField::new('compteBancaires', PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_DATE_DE_CREATION)) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_FACTURE_DATE_DE_CREATION)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION)) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION)->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_UTILISATEUR)) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_FACTURE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])->onlyOnDetail();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_ENTREPRISE)) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_FACTURE_ENTREPRISE)->onlyOnDetail();
        }
        return $tabAttributs;
    }

    public function setFIN_Fields_Facture_form($tabAttributs)
    {
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_TYPE)) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
                ->setChoices(FactureCrudController::TAB_TYPE_FACTURE)
                ->setColumns(4)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)) {
            $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
                ->setColumns(4)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)) {
            $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
                ->setRequired(false)
                ->setColumns(4)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)
                ->setRequired(false)
                ->setColumns(4)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)) {
            $tabAttributs[] = TextField::new('autreTiers', PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)
                ->setRequired(false)
                ->setColumns(4)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_DESCRIPTION)) {
            $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_FIN_FACTURE_DESCRIPTION)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)) {
            $tabAttributs[] = CollectionField::new('elementFactures', PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)
                ->useEntryCrudForm(ElementFactureCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setEntryIsComplex()
                ->setRequired(false)
                ->setColumns(6)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)) {
            $tabAttributs[] = AssociationField::new('compteBancaires', PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
                ->setRequired(false)
                ->setColumns(6)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_PIECE)) {
            $tabAttributs[] = AssociationField::new('piece', PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY)) {
            $tabAttributs[] = TextField::new('signedBy', PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY)
                ->setRequired(true)
                ->setColumns(6)
                ->onlyOnForms();
        }
        if ($this->canShow_url(PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY)) {
            $tabAttributs[] = TextField::new('posteSignedBy', PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY)
                ->setRequired(true)
                ->setColumns(6)
                ->onlyOnForms();
        }
        //On désactive les champs non éditables
        $this->appliquerCanDesable($tabAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Produits_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_PRODUIT_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
            ->onlyOnDetail();
        $tabAttributs[] = PercentField::new('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
            ->setNumDecimals(2)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('obligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('abonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('iard', PreferenceCrudController::PREF_PRO_PRODUIT_IARD)
            ->setChoices(["IARD (Non Vie)" => 1, "VIE" => 0])
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('cotations', PreferenceCrudController::PREF_PRO_PRODUIT_COTATIONS)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('police', PreferenceCrudController::PREF_PRO_PRODUIT_POLICES)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PRODUIT_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PRODUIT_ENTREPRISE)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION)
            ->onlyOnDetail();

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_details(false, $tabAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Produits_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_PRODUIT_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_CODE])) {
            $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_IARD])) {
            $tabAttributs[] = ChoiceField::new('iard', PreferenceCrudController::PREF_PRO_PRODUIT_IARD)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_IARD)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE])) {
            $tabAttributs[] = ChoiceField::new('obligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT])) {
            $tabAttributs[] = ChoiceField::new('abonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
                ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION])) {
            $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION])) {
            $tabAttributs[] = PercentField::new('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
                ->setNumDecimals(2)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_COTATIONS])) {
            $tabAttributs[] = AssociationField::new('cotations', PreferenceCrudController::PREF_PRO_PRODUIT_COTATIONS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_POLICES])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_PRO_PRODUIT_POLICES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PRODUIT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PRODUIT_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_Index(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Taxes_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_TAXE_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_TAXE_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = PercentField::new('tauxIARD', PreferenceCrudController::PREF_FIN_TAXE_TAUX_IARD)
            ->setNumDecimals(2)
            ->onlyOnDetail();
        $tabAttributs[] = PercentField::new('tauxVIE', PreferenceCrudController::PREF_FIN_TAXE_TAUX_VIE)
            ->setNumDecimals(2)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('organisation', PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('payableparcourtier', PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER)
            ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_TAXE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_TAXE_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_TAXE_DERNIERE_MODIFICATION)
            ->onlyOnDetail();
        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_CalculablesTaxes_Details($tabAttributs);
        return $tabAttributs;
    }

    public function setCRM_Fields_Taxes_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_TAXE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_TAXE_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_TAUX_IARD])) {
            $tabAttributs[] = PercentField::new('tauxIARD', PreferenceCrudController::PREF_FIN_TAXE_TAUX_IARD)
                ->setNumDecimals(2)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_TAUX_VIE])) {
            $tabAttributs[] = PercentField::new('tauxVIE', PreferenceCrudController::PREF_FIN_TAXE_TAUX_VIE)
                ->setNumDecimals(2)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION])) {
            $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION])) {
            $tabAttributs[] = TextField::new('organisation', PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER])) {
            $tabAttributs[] = ChoiceField::new('payableparcourtier', PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER)
                ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_TAXE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_TAXE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_TAXE_DERNIERE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_TAXE_DERNIERE_MODIFICATION)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_CalculablesTaxes_Index($tabAttributs, $tabPreferences, $tabDefaultAttributs);
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
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_CLIENT)) {
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
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)) {
            $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_PRO_POLICE_PRODUIT)
                ->onlyOnForms()
                ->setRequired(false)
                ->setColumns(6)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_PRO_POLICE_ASSUREURS)
                ->onlyOnForms()
                ->setRequired(false)
                ->setColumns(6)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        $tabAttributs[] = TextField::new('reassureurs', PreferenceCrudController::PREF_PRO_POLICE_REASSUREURS)
            ->onlyOnForms()
            ->setColumns(6);
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_COTATION)) {
            $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)
                ->onlyOnForms()
                ->setColumns(6)
                ->setRequired(false)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_PISTES)) {
            $tabAttributs[] = AssociationField::new('pistes', PreferenceCrudController::PREF_PRO_POLICE_PISTES)
                ->onlyOnForms()
                ->setColumns(6)
                ->setRequired(false)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)) {
            $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                ->onlyOnForms()
                ->setColumns(6)
                ->setRequired(false)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }

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

        $tabAttributs[] = FormField::addTab(' Prime & Capitaux')
            ->setIcon('fas fa-bag-shopping')
            ->setHelp("Le contrat d'assurance en place.")
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('capital', PreferenceCrudController::PREF_PRO_POLICE_CAPITAL)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = ChoiceField::new('modepaiement', PreferenceCrudController::PREF_PRO_POLICE_MODE_PAIEMENT)
            ->setColumns(2)
            ->onlyOnForms()
            ->setChoices(PoliceCrudController::TAB_POLICE_MODE_PAIEMENT);
        $tabAttributs[] = FormField::addPanel('Facture client')->onlyOnForms();
        $tabAttributs[] = MoneyField::new('primenette', PreferenceCrudController::PREF_PRO_POLICE_PRIME_NETTE)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = MoneyField::new('fronting', PreferenceCrudController::PREF_PRO_POLICE_FRONTING)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = MoneyField::new('arca', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_COURTIER, PreferenceCrudController::PREF_PRO_POLICE_ARCA))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = MoneyField::new('tva', $this->getTitreAttributTaxe_Simple(self::INDICE_TAXE_ASSUREUR, PreferenceCrudController::PREF_PRO_POLICE_TVA))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = MoneyField::new('fraisadmin', PreferenceCrudController::PREF_PRO_POLICE_FRAIS_ADMIN)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = MoneyField::new('discount', PreferenceCrudController::PREF_PRO_POLICE_DISCOUNT)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = MoneyField::new('primetotale', PreferenceCrudController::PREF_PRO_POLICE_PRIME_TOTALE)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = FormField::addTab(' Structure des revenus')
            ->setIcon('fas fa-sack-dollar')
            ->onlyOnForms();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_PARTENAIRE)) {
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
        }
        $tabAttributs[] = PercentField::new('partExceptionnellePartenaire', PreferenceCrudController::PREF_PRO_POLICE_PART_EXCEPTIONNELLE)
            ->onlyOnForms()
            ->setNumDecimals(2)
            ->setColumns(3)
            ->setHelp("Précisez le taux exceptionnel si, pour ce compte spécifique, le taux est différent du standard.");
        $tabAttributs[] = FormField::addPanel('Commission de réassurance hors taxe')
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('ricom', PreferenceCrudController::PREF_PRO_POLICE_RI_COM)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
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
        $tabAttributs[] = FormField::addPanel("Commission locale hors taxe")->onlyOnForms();
        $tabAttributs[] = MoneyField::new('localcom', PreferenceCrudController::PREF_PRO_POLICE_LOCAL_COM)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
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
        $tabAttributs[] = FormField::addPanel("Commission hors taxe sur Fronting")
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('frontingcom', PreferenceCrudController::PREF_PRO_POLICE_FRONTIN_COM) //"Montant ht"
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
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
        $tabAttributs[] = TextEditorField::new('remarques', PreferenceCrudController::PREF_PRO_POLICE_REMARQUE)
            ->onlyOnForms()
            ->setColumns(12);

        //On désactive les champs non éditables
        $this->appliquerCanDesable($tabAttributs);

        return $tabAttributs;
    }

    public function appliquerCanDesable($tabAttributs)
    {
        foreach ($tabAttributs as $champ) {
            $champ->setDisabled($this->canDesable($this->adminUrlGenerator, $champ->getAsDto()->getLabel()));
        }
    }

    public function setCRM_Fields_Produits_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
            ->setColumns(1)
            ->onlyOnForms();
        $tabAttributs[] = PercentField::new('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
            ->setNumDecimals(2)
            ->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = BooleanField::new('obligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
            ->setColumns(12)
            //->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
            ->onlyOnForms();
        $tabAttributs[] = BooleanField::new('abonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
            ->setColumns(12)
            //->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('iard', PreferenceCrudController::PREF_PRO_PRODUIT_IARD)
            ->setColumns(12)
            ->setChoices(["IARD (Non Vie)" => 1, "VIE" => 0])
            ->onlyOnForms();
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
            ->setColumns(12)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_Taxes_form($tabAttributs)
    {

        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_TAXE_NOM)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = PercentField::new('tauxIARD', PreferenceCrudController::PREF_FIN_TAXE_TAUX_IARD)
            ->setColumns(12)
            ->setNumDecimals(2)
            ->onlyOnForms();
        $tabAttributs[] = PercentField::new('tauxVIE', PreferenceCrudController::PREF_FIN_TAXE_TAUX_VIE)
            ->setColumns(12)
            ->setNumDecimals(2)
            ->onlyOnForms();
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('organisation', PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('payableparcourtier', PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER)
            ->setColumns(12)
            ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_Monnaies_form($tabAttributs)
    {
        $tabAttributs[] = ChoiceField::new('code', PreferenceCrudController::PREF_FIN_MONNAIE_NOM)
            ->setColumns(6)
            ->setChoices(MonnaieCrudController::TAB_MONNAIES)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('fonction', PreferenceCrudController::PREF_FIN_MONNAIE_FONCTION)
            ->setColumns(2)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_FONCTIONS)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('tauxusd', PreferenceCrudController::PREF_FIN_MONNAIE_TAUX_USD)
            ->setCurrency("USD")
            ->setStoredAsCents()
            ->setNumDecimals(4)
            ->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('islocale', PreferenceCrudController::PREF_FIN_MONNAIE_IS_LOCALE)
            ->setColumns(2)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setFIN_Fields_CompteBancaire_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('intitule', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_INTITULE)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('codeMonnaie', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_MONNAIE)
            ->setColumns(6)
            ->setChoices(MonnaieCrudController::TAB_MONNAIES)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('numero', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_NUMERO)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('banque', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_BANQUE)
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('codeSwift', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_CODESWIFT)
            ->setColumns(3)
            ->onlyOnForms();

        return $tabAttributs;
    }


    public function setCRM_Fields_EtapeSinistres_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_ETAPE_NOM)
            ->setColumns(10)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('indice', PreferenceCrudController::PREF_SIN_ETAPE_INDICE)
            ->setColumns(2)
            ->setChoices(EtapeSinistreCrudController::TAB_ETAPE_INDICE)
            ->onlyOnForms();
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_SIN_ETAPE_DESCRIPTION)
            ->setColumns(12)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_ExpertSinistres_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_EXPERT_NOM)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_SIN_EXPERT_ADRESSE)
            ->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_SIN_EXPERT_EMAIL)
            ->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_SIN_EXPERT_SITE_INTERNET)
            ->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_SIN_EXPERT_TELEPHONE)
            ->setColumns(2)
            ->onlyOnForms();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_SIN_EXPERT_SINISTRES)) {
            $tabAttributs[] = AssociationField::new('sinistres', PreferenceCrudController::PREF_SIN_EXPERT_SINISTRES)
                ->setColumns(12)
                ->onlyOnForms();
        }
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_SIN_EXPERT_DESCRIPTION)
            ->setColumns(12)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_SinistreSinistres_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('titre', PreferenceCrudController::PREF_SIN_SINISTRE_ITITRE)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('numero', PreferenceCrudController::PREF_SIN_SINISTRE_REFERENCE)
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_SIN_SINISTRE_ETAPE)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            })
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('cout', PreferenceCrudController::PREF_SIN_SINISTRE_COUT)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = DateField::new('occuredAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_OCCURENCE)
            ->setColumns(2)
            ->onlyOnForms();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_SIN_SINISTRE_EXPERT)) {
            $tabAttributs[] = AssociationField::new('experts', PreferenceCrudController::PREF_SIN_SINISTRE_EXPERT)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns(12)
                ->onlyWhenUpdating();
        }
        $tabAttributs[] = AssociationField::new('victimes', PreferenceCrudController::PREF_SIN_SINISTRE_VICTIMES)
            ->setHelp("Si la victime ne se trouve pas dans cette liste, ne vous inquiètez pas car vous pouvez en ajouter à tout moment après l'enregistrement de ce sinistre.")
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            })
            ->setColumns(12)
            ->onlyWhenUpdating();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_SIN_SINISTRE_POLICE)) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_SIN_SINISTRE_POLICE)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns(12)
                ->onlyOnForms();
        }
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_SIN_SINISTRE_DESCRIPTION)
            ->setColumns(12)
            ->onlyOnForms();

        $tabAttributs[] = MoneyField::new('montantPaye', PreferenceCrudController::PREF_SIN_SINISTRE_MONTANT_PAYE)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setColumns(2)
            ->onlyWhenUpdating();
        $tabAttributs[] = DateTimeField::new('paidAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_PAIEMENT)
            ->setColumns(2)
            ->onlyWhenUpdating();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_SIN_SINISTRE_DOCUMENTS)) {
            $tabAttributs[] = AssociationField::new('docPieces', PreferenceCrudController::PREF_SIN_SINISTRE_DOCUMENTS)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setColumns(12)
                ->onlyWhenUpdating();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_SinistreVictimes_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_VICTIME_NOM)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_SIN_VICTIME_ADRESSE)
            ->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_SIN_VICTIME_EMAIL)
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_SIN_VICTIME_TELEPHONE)
            ->setColumns(3)
            ->onlyOnForms();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_SIN_VICTIME_SINISTRE)) {
            $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_SIN_VICTIME_SINISTRE)
                ->setColumns(12)
                ->onlyOnForms();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequeCategories_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_CATEGORIE_NOM)
            ->setColumns(6)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequeClasseurs_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_CLASSEUR_NOM)
            ->setColumns(6)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequePieces_form($tabAttributs)
    {
        //dd($this->adminUrlGenerator);
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_DOCUMENT_NOM)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_BIB_DOCUMENT_CATEGORIE)) {
            $tabAttributs[] = AssociationField::new('categorie', PreferenceCrudController::PREF_BIB_DOCUMENT_CATEGORIE)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                //->setColumns(3)
                ->setColumns(12)
                ->setRequired(false)
                ->onlyOnForms();
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_BIB_DOCUMENT_CLASSEUR)) {
            $tabAttributs[] = AssociationField::new('classeur', PreferenceCrudController::PREF_BIB_DOCUMENT_CLASSEUR)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                //->setColumns(3)
                ->setColumns(12)
                ->setRequired(false)
                ->onlyOnForms();
        }
        $tabAttributs[] = ImageField::new('fichier', 'Fichier')
            ->setBasePath(DocPieceCrudController::ARTICLE_BASE_PATH)
            ->setUploadDir(DocPieceCrudController::ARTICLE_UPLOAD_DIR)
            ->setSortable(false)
            //->setColumns(12)
            ->setColumns(12)
            ->onlyOnForms();
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_BIB_DOCUMENT_DESCRIPTION)) {
            $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_BIB_DOCUMENT_DESCRIPTION)
                //->setColumns(12)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)) {
            $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setRequired(false)
                //->setColumns(12)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setRequired(false)
                //->setColumns(12)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)) {
            $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                })
                ->setRequired(false)
                //->setColumns(12)
                ->setColumns(12)
                ->onlyOnForms();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_ParUtilisateurs_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PAR_UTILISATEUR_NOM)
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('pseudo', PreferenceCrudController::PREF_PAR_UTILISATEUR_PSEUDO)
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('email', PreferenceCrudController::PREF_PAR_UTILISATEUR_EMAIL)
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('plainPassword', 'Nouveau mot de passe')
            ->setEmptyData('')
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('roles', "Roles")
            ->setChoices(UtilisateurCrudController::TAB_ROLES)
            ->allowMultipleChoices()
            ->renderExpanded()
            ->renderAsBadges([
                // $value => $badgeStyleName
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE] => 'success', //info
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION] => 'danger',
            ])
            ->setColumns(3)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_Monnaies_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_MONNAIE_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_MONNAIE_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_FIN_MONNAIE_CODE)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('fonction', PreferenceCrudController::PREF_FIN_MONNAIE_FONCTION)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_FONCTIONS)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('tauxusd', PreferenceCrudController::PREF_FIN_MONNAIE_TAUX_USD)
            ->setCurrency("USD")
            ->setStoredAsCents()
            ->setNumDecimals(4)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('islocale', PreferenceCrudController::PREF_FIN_MONNAIE_IS_LOCALE)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_MONNAIE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_MONNAIE_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_MONNAIE_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_MONNAIE_ENTREPRISE)
            ->onlyOnDetail();
        return $tabAttributs;
    }

    public function setCRM_Fields_Monnaies_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_MONNAIE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_MONNAIE_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_CODE])) {
            $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_FIN_MONNAIE_CODE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_FONCTION])) {
            $tabAttributs[] = ChoiceField::new('fonction', PreferenceCrudController::PREF_FIN_MONNAIE_FONCTION)
                ->setChoices(MonnaieCrudController::TAB_MONNAIE_FONCTIONS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_TAUX_USD])) {
            $tabAttributs[] = MoneyField::new('tauxusd', PreferenceCrudController::PREF_FIN_MONNAIE_TAUX_USD)
                ->setCurrency("USD")
                ->setStoredAsCents()
                ->setNumDecimals(4)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_IS_LOCALE])) {
            $tabAttributs[] = ChoiceField::new('islocale', PreferenceCrudController::PREF_FIN_MONNAIE_IS_LOCALE)
                ->setChoices(MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_MONNAIE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_UTILISATEUR])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_MONNAIE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_MONNAIE_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_MONNAIE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_MONNAIE_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }


    public function setCRM_Fields_EtapeSinistres_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_ETAPE_ID)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('indice', PreferenceCrudController::PREF_SIN_ETAPE_INDICE)
            ->setChoices(EtapeSinistreCrudController::TAB_ETAPE_INDICE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_ETAPE_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('sinistres', PreferenceCrudController::PREF_SIN_ETAPE_SINISTRES)
            ->onlyOnDetail();
        $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_SIN_ETAPE_DESCRIPTION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_ETAPE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_ETAPE_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_ETAPE_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_ETAPE_ENTREPRISE)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_EtapeSinistres_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_ETAPE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_ETAPE_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_INDICE])) {
            $tabAttributs[] = ChoiceField::new('indice', PreferenceCrudController::PREF_SIN_ETAPE_INDICE)
                ->setChoices(EtapeSinistreCrudController::TAB_ETAPE_INDICE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_SINISTRES])) {
            $tabAttributs[] = AssociationField::new('sinistres', PreferenceCrudController::PREF_SIN_ETAPE_SINISTRES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_DESCRIPTION])) {
            $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_SIN_ETAPE_DESCRIPTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_ETAPE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_ETAPE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_ETAPE_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_ETAPE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_ETAPE_ENTREPRISE)
                ->onlyOnIndex();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_ExpertSinistres_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_EXPERT_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_EXPERT_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('sinistres', PreferenceCrudController::PREF_SIN_EXPERT_SINISTRES)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_SIN_EXPERT_ADRESSE)
            ->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_SIN_EXPERT_EMAIL)
            ->onlyOnDetail();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_SIN_EXPERT_SITE_INTERNET)
            ->onlyOnDetail();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_SIN_EXPERT_TELEPHONE)
            ->onlyOnDetail();
        $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_SIN_EXPERT_DESCRIPTION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_EXPERT_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_EXPERT_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_EXPERT_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_EXPERT_ENTREPRISE)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_ExpertSinistres_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_EXPERT_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_EXPERT_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_SINISTRES])) {
            $tabAttributs[] = AssociationField::new('sinistres', PreferenceCrudController::PREF_SIN_EXPERT_SINISTRES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_SIN_EXPERT_ADRESSE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_SIN_EXPERT_EMAIL)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_SITE_INTERNET])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_SIN_EXPERT_SITE_INTERNET)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_SIN_EXPERT_TELEPHONE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_DESCRIPTION])) {
            $tabAttributs[] = TextareaField::new('description', PreferenceCrudController::PREF_SIN_EXPERT_DESCRIPTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_EXPERT_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_EXPERT_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_EXPERT_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_EXPERT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_EXPERT_ENTREPRISE)
                ->onlyOnIndex();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_SinistreSinistres_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_SINISTRE_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('titre', PreferenceCrudController::PREF_SIN_SINISTRE_ITITRE)
            ->onlyOnDetail();
        //On doit afficher la référence sans aucune restriction / condition
        $tabAttributs[] = TextField::new('numero', PreferenceCrudController::PREF_SIN_SINISTRE_REFERENCE)
            ->formatValue(function ($value, Sinistre $sinistre) {
                $this->setTitreReportingSinistre($sinistre);
                return $value;
            })
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_SIN_SINISTRE_ETAPE)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('victimes', PreferenceCrudController::PREF_SIN_SINISTRE_VICTIMES)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('experts', PreferenceCrudController::PREF_SIN_SINISTRE_EXPERT)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('docPieces', PreferenceCrudController::PREF_SIN_SINISTRE_DOCUMENTS)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('actionCRMs', PreferenceCrudController::PREF_SIN_SINISTRE_ACTIONS)
            ->onlyOnDetail();
        $tabAttributs[] = DateField::new('occuredAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_OCCURENCE)
            ->onlyOnDetail();
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_SIN_SINISTRE_DESCRIPTION)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('cout', PreferenceCrudController::PREF_SIN_SINISTRE_COUT)
            ->formatValue(function ($value, Sinistre $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getCout());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('montantPaye', PreferenceCrudController::PREF_SIN_SINISTRE_MONTANT_PAYE)
            ->formatValue(function ($value, Sinistre $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontantPaye());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('paidAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_PAIEMENT)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_SIN_SINISTRE_POLICE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_SINISTRE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_SINISTRE_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_SINISTRE_ENTREPRISE)
            ->onlyOnDetail();

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_details(false, $tabAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_SinistreSinistres_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_SINISTRE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_ITITRE])) {
            $tabAttributs[] = TextField::new('titre', PreferenceCrudController::PREF_SIN_SINISTRE_ITITRE)
                ->onlyOnIndex();
        }
        //On doit afficher la référence sans aucune restriction / condition
        $tabAttributs[] = TextField::new('numero', PreferenceCrudController::PREF_SIN_SINISTRE_REFERENCE)
            ->formatValue(function ($value, Sinistre $sinistre) {
                $this->setTitreReportingSinistre($sinistre);
                return $value;
            })
            ->onlyOnIndex();

        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_ETAPE])) {
            $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_SIN_SINISTRE_ETAPE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_VICTIMES])) {
            $tabAttributs[] = AssociationField::new('victimes', PreferenceCrudController::PREF_SIN_SINISTRE_VICTIMES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_EXPERT])) {
            $tabAttributs[] = AssociationField::new('experts', PreferenceCrudController::PREF_SIN_SINISTRE_EXPERT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_DOCUMENTS])) {
            $tabAttributs[] = AssociationField::new('docPieces', PreferenceCrudController::PREF_SIN_SINISTRE_DOCUMENTS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_ACTIONS])) {
            $tabAttributs[] = AssociationField::new('actionCRMs', PreferenceCrudController::PREF_SIN_SINISTRE_ACTIONS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_DATE_OCCURENCE])) {
            $tabAttributs[] = DateField::new('occuredAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_OCCURENCE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_DESCRIPTION])) {
            $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_SIN_SINISTRE_DESCRIPTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_COUT])) {
            $tabAttributs[] = MoneyField::new('cout', PreferenceCrudController::PREF_SIN_SINISTRE_COUT)
                ->formatValue(function ($value, Sinistre $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getCout());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_MONTANT_PAYE])) {
            $tabAttributs[] = MoneyField::new('montantPaye', PreferenceCrudController::PREF_SIN_SINISTRE_MONTANT_PAYE)
                ->formatValue(function ($value, Sinistre $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontantPaye());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_DATE_PAIEMENT])) {
            $tabAttributs[] = DateTimeField::new('paidAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_PAIEMENT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_POLICE])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_SIN_SINISTRE_POLICE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_SINISTRE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_SINISTRE_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_SINISTRE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_SINISTRE_ENTREPRISE)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_Index(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_SinistreVictimes_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_VICTIME_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_VICTIME_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_SIN_VICTIME_SINISTRE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_SIN_VICTIME_ADRESSE)
            ->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_SIN_VICTIME_EMAIL)
            ->onlyOnDetail();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_SIN_VICTIME_TELEPHONE)
            ->onlyOnDetail();
        $tabAttributs[] =  AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_VICTIME_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] =  DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_VICTIME_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] =  DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_VICTIME_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] =  AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_VICTIME_ENTREPRISE)
            ->onlyOnDetail();
        return $tabAttributs;
    }

    public function setCRM_Fields_SinistreVictimes_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_SIN_VICTIME_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_SIN_VICTIME_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_SINISTRE])) {
            $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_SIN_VICTIME_SINISTRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_SIN_VICTIME_ADRESSE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_SIN_VICTIME_EMAIL)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_SIN_VICTIME_TELEPHONE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_UTILISATEUR])) {
            $tabAttributs[] =  AssociationField::new('utilisateur', PreferenceCrudController::PREF_SIN_VICTIME_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_DATE_DE_CREATION])) {
            $tabAttributs[] =  DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_VICTIME_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_DERNIRE_MODIFICATION])) {
            $tabAttributs[] =  DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_VICTIME_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_SIN_VICTIME_ENTREPRISE])) {
            $tabAttributs[] =  AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_VICTIME_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }


    public function setCRM_Fields_BibliothequeCategories_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_BIB_CATEGORIE_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_CATEGORIE_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('docPieces', PreferenceCrudController::PREF_BIB_CATEGORIE_PIECES)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_BIB_CATEGORIE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_BIB_CATEGORIE_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_BIB_CATEGORIE_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_BIB_CATEGORIE_ENTREPRISE)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequeCategories_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CATEGORIE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_BIB_CATEGORIE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CATEGORIE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_CATEGORIE_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CATEGORIE_PIECES])) {
            $tabAttributs[] = AssociationField::new('docPieces', PreferenceCrudController::PREF_BIB_CATEGORIE_PIECES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CATEGORIE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_BIB_CATEGORIE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CATEGORIE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_BIB_CATEGORIE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CATEGORIE_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_BIB_CATEGORIE_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CATEGORIE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_BIB_CATEGORIE_ENTREPRISE)
                ->onlyOnIndex();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequeClasseurs_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_BIB_CLASSEUR_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_CLASSEUR_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('docPieces', PreferenceCrudController::PREF_BIB_CLASSEUR_PIECES)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_BIB_CLASSEUR_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_BIB_CLASSEUR_ENTREPRISE)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequeClasseurs_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_BIB_CLASSEUR_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_CLASSEUR_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_PIECES])) {
            $tabAttributs[] = AssociationField::new('docPieces', PreferenceCrudController::PREF_BIB_CLASSEUR_PIECES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_BIB_CLASSEUR_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_BIB_CLASSEUR_ENTREPRISE)
                ->onlyOnIndex();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequePieces_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_BIB_DOCUMENT_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_DOCUMENT_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_BIB_DOCUMENT_DESCRIPTION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('categorie', PreferenceCrudController::PREF_BIB_DOCUMENT_CATEGORIE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('classeur', PreferenceCrudController::PREF_BIB_DOCUMENT_CLASSEUR)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)
            ->onlyOnDetail();
        //Les fichiers
        $tabAttributs[] = TextField::new('fichierA', 'Fichier A')->onlyOnDetail();
        $tabAttributs[] = TextField::new('fichierB', 'Fichier B')->onlyOnDetail();
        $tabAttributs[] = TextField::new('fichierC', 'Fichier C')->onlyOnDetail();
        $tabAttributs[] = TextField::new('fichierD', 'Fichier D')->onlyOnDetail();
        //Fin  - les fichiers
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_BIB_CLASSEUR_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_BIB_CLASSEUR_ENTREPRISE)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequePieces_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_BIB_DOCUMENT_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_DOCUMENT_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_DESCRIPTION])) {
            $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_BIB_DOCUMENT_DESCRIPTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_CATEGORIE])) {
            $tabAttributs[] = ArrayField::new('categorie', PreferenceCrudController::PREF_BIB_DOCUMENT_CATEGORIE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_CLASSEUR])) {
            $tabAttributs[] = ArrayField::new('classeur', PreferenceCrudController::PREF_BIB_DOCUMENT_CLASSEUR)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION])) {
            $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_BIB_DOCUMENT_COTATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_BIB_DOCUMENT_POLICE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE])) {
            $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_BIB_DOCUMENT_SINISTRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_BIB_CLASSEUR_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_BIB_CLASSEUR_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_BIB_CLASSEUR_ENTREPRISE)
                ->onlyOnIndex();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_ParUtilisateurs_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PAR_UTILISATEUR_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PAR_UTILISATEUR_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('pseudo', PreferenceCrudController::PREF_PAR_UTILISATEUR_PSEUDO)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('email', PreferenceCrudController::PREF_PAR_UTILISATEUR_EMAIL)
            ->onlyOnDetail();
        $tabAttributs[] = $tabAttributs[] = ChoiceField::new('roles', PreferenceCrudController::PREF_PAR_UTILISATEUR_ROLES)
            ->setChoices(UtilisateurCrudController::TAB_ROLES)
            ->renderAsBadges([
                // $value => $badgeStyleName
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE] => 'success', //info
                UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION] => 'danger',
            ])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PAR_UTILISATEUR_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('actionCRMs', PreferenceCrudController::PREF_PAR_UTILISATEUR_MISSIONS)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PAR_UTILISATEUR_ENTREPRISE)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PAR_UTILISATEUR_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PAR_UTILISATEUR_DERNIRE_MODIFICATION)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_ParUtilisateurs_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PAR_UTILISATEUR_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PAR_UTILISATEUR_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_PSEUDO])) {
            $tabAttributs[] = TextField::new('pseudo', PreferenceCrudController::PREF_PAR_UTILISATEUR_PSEUDO)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_EMAIL])) {
            $tabAttributs[] = TextField::new('email', PreferenceCrudController::PREF_PAR_UTILISATEUR_EMAIL)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_ROLES])) {
            $tabAttributs[] = $tabAttributs[] = ChoiceField::new('roles', PreferenceCrudController::PREF_PAR_UTILISATEUR_ROLES)
                ->setChoices(UtilisateurCrudController::TAB_ROLES)
                ->allowMultipleChoices()
                ->renderExpanded()
                ->renderAsBadges([
                    // $value => $badgeStyleName
                    UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE] => 'success', //info
                    UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACTION_EDITION] => 'danger',
                ])
                ->setColumns(3)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PAR_UTILISATEUR_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_MISSIONS])) {
            $tabAttributs[] = AssociationField::new('actionCRMs', PreferenceCrudController::PREF_PAR_UTILISATEUR_MISSIONS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PAR_UTILISATEUR_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PAR_UTILISATEUR_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PAR_UTILISATEUR_DERNIRE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PAR_UTILISATEUR_DERNIRE_MODIFICATION)
                ->onlyOnIndex();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_Partenaires_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)
            ->setColumns(12)
            //->setColumns(10)
            ->onlyOnForms();
        $tabAttributs[] = PercentField::new('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)
            ->setNumDecimals(2)
            ->setColumns(12)
            //->setColumns(2)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)
            ->setColumns(12)
            //->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)
            ->setColumns(12)
            //->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)
            ->setColumns(12)
            //->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)
            ->setColumns(12)
            //->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)
            ->setColumns(12)
            //->setColumns(4)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)
            ->setColumns(12)
            //->setColumns(4)
            ->onlyOnForms();

        return $tabAttributs;
    }

    public function setCRM_Fields_Partenaires_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_PARTENAIRE_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)->onlyOnDetail();
        $tabAttributs[] = PercentField::new('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)->setNumDecimals(2)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('police', PreferenceCrudController::PREF_PRO_PARTENAIRE_POLICES)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('paiementPartenaires', PreferenceCrudController::PREF_PRO_PARTENAIRE_POP_PARTENAIRE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)->onlyOnDetail();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)->onlyOnDetail();
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)->onlyOnDetail();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)->onlyOnDetail();
        $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PARTENAIRE_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PARTENAIRE_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION)->onlyOnDetail();

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_details(false, $tabAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Partenaires_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_PARTENAIRE_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_PART])) {
            $tabAttributs[] = PercentField::new('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)
                ->setNumDecimals(2)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_POLICES])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_PRO_PARTENAIRE_POLICES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_POP_PARTENAIRE])) {
            $tabAttributs[] = AssociationField::new('paiementPartenaires', PreferenceCrudController::PREF_PRO_PARTENAIRE_POP_PARTENAIRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM])) {
            $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT])) {
            $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT])) {
            $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PARTENAIRE_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PARTENAIRE_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_Index(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Clients_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_CLIENT_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CLIENT_NOM)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('cotations', PreferenceCrudController::PREF_PRO_CLIENT_COTATIONS)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('police', PreferenceCrudController::PREF_PRO_CLIENT_POLICES)->onlyOnDetail();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE)->onlyOnDetail();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)->onlyOnDetail();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
            ->onlyOnDetail()
            ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE);
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_CLIENT_RCCM)->onlyOnDetail();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_CLIENT_IDNAT)->onlyOnDetail();
        $tabAttributs[] = TextField::new('numipot', PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
            ->onlyOnDetail()
            ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR);
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION)->onlyOnDetail();
        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_details(false, $tabAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Clients_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_CLIENT_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CLIENT_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_EXONEREE])) {
            $tabAttributs[] = ChoiceField::new('exoneree', PreferenceCrudController::PREF_PRO_CLIENT_EXONEREE)
                ->setChoices([
                    'Non' => 0,
                    'Oui' => 1
                ])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_POLICES])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_PRO_CLIENT_POLICES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE])) {
            $tabAttributs[] = ChoiceField::new('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
                ->onlyOnIndex()
                ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_RCCM])) {
            $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_CLIENT_RCCM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_IDNAT])) {
            $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_CLIENT_IDNAT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT])) {
            $tabAttributs[] = TextField::new('numipot', PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR])) {
            $tabAttributs[] = ChoiceField::new('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
                ->onlyOnIndex()
                ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }
        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_Index(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Clients_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CLIENT_NOM)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_CLIENT_ADRESSE)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)
            ->setColumns(12)
            //->setColumns(6)
            ->onlyOnForms();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms()
            ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE);
        $tabAttributs[] = BooleanField::new('exoneree', PreferenceCrudController::PREF_PRO_CLIENT_EXONEREE)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_CLIENT_RCCM)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_CLIENT_IDNAT)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('numipot', PreferenceCrudController::PREF_PRO_CLIENT_NUM_IMPOT)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
            //->setColumns(6)
            ->setColumns(12)
            ->onlyOnForms()
            ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR);

        return $tabAttributs;
    }

    public function setCRM_Fields_Engins_form($tabAttributs)
    {
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_ENGIN_POLICE)) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_PRO_ENGIN_POLICE)
                ->setRequired(false)
                ->onlyOnForms()
                ->setColumns(12)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        $tabAttributs[] = TextField::new('plaque', PreferenceCrudController::PREF_PRO_ENGIN_N°_PLAQUE)
            ->onlyOnForms()
            ->setColumns(4);
        $tabAttributs[] = TextField::new('chassis', PreferenceCrudController::PREF_PRO_ENGIN_N°_CHASSIS)
            ->onlyOnForms()
            ->setColumns(4);
        $tabAttributs[] = TextField::new('model', PreferenceCrudController::PREF_PRO_ENGIN_MODEL)
            ->onlyOnForms()
            ->setColumns(4);
        $tabAttributs[] = TextField::new('marque', PreferenceCrudController::PREF_PRO_ENGIN_MARQUE)
            ->onlyOnForms()
            ->setColumns(4);
        $tabAttributs[] = TextField::new('annee', PreferenceCrudController::PREF_PRO_ENGIN_ANNEE)
            ->onlyOnForms()
            ->setColumns(4);
        $tabAttributs[] = TextField::new('puissance', PreferenceCrudController::PREF_PRO_ENGIN_PUISSANCE)
            ->onlyOnForms()
            ->setColumns(4);
        $tabAttributs[] = MoneyField::new('valeur', PreferenceCrudController::PREF_PRO_ENGIN_VALEUR)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = NumberField::new('nbsieges', PreferenceCrudController::PREF_PRO_ENGIN_NB_SIEGES)
            ->onlyOnForms()
            ->setColumns(2);
        $tabAttributs[] = ChoiceField::new('utilite', PreferenceCrudController::PREF_PRO_ENGIN_USAGE)
            ->onlyOnForms()
            ->setColumns(4)
            ->setChoices(AutomobileCrudController::TAB_AUTO_UTILITE);
        $tabAttributs[] = ChoiceField::new('nature', PreferenceCrudController::PREF_PRO_ENGIN_NATURE)
            ->onlyOnForms()
            ->setColumns(4)
            ->setChoices(AutomobileCrudController::TAB_AUTO_NATURE);

        return $tabAttributs;
    }

    public function setCRM_Fields_Contacts_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_CONTACT_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CONTACT_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_POSTE])) {
            $tabAttributs[] = TextField::new('poste', PreferenceCrudController::PREF_PRO_CONTACT_POSTE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CONTACT_EMAIL)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_PRO_CONTACT_PISTE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_CONTACT_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_CONTACT_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Contacts_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_CONTACT_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CONTACT_NOM)->onlyOnDetail();
        $tabAttributs[] = TextField::new('poste', PreferenceCrudController::PREF_PRO_CONTACT_POSTE)->onlyOnDetail();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE)->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CONTACT_EMAIL)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_PRO_CONTACT_PISTE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_CONTACT_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_CONTACT_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_MODIFICATION)->onlyOnDetail();

        return $tabAttributs;
    }


    public function setCRM_Fields_Contacts_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CONTACT_NOM)
            ->onlyOnForms()
            ->setColumns(12);
        //->setColumns(6);
        $tabAttributs[] = TextField::new('poste', PreferenceCrudController::PREF_PRO_CONTACT_POSTE)
            ->onlyOnForms()
            ->setColumns(12);
        //->setColumns(6);
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE)
            ->onlyOnForms()
            ->setColumns(12);
        //->setColumns(6);
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CONTACT_EMAIL)
            ->onlyOnForms()
            ->setColumns(12);
        //->setColumns(6);
        return $tabAttributs;
    }

    public function setCRM_Fields_Engins_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ENGIN_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_N°_PLAQUE])) {
            $tabAttributs[] = TextField::new('plaque', PreferenceCrudController::PREF_PRO_ENGIN_N°_PLAQUE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_POLICE])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_PRO_ENGIN_POLICE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_N°_CHASSIS])) {
            $tabAttributs[] = TextField::new('chassis', PreferenceCrudController::PREF_PRO_ENGIN_N°_CHASSIS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_MODEL])) {
            $tabAttributs[] = TextField::new('model', PreferenceCrudController::PREF_PRO_ENGIN_MODEL)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_MARQUE])) {
            $tabAttributs[] = TextField::new('marque', PreferenceCrudController::PREF_PRO_ENGIN_MARQUE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_ANNEE])) {
            $tabAttributs[] = TextField::new('annee', PreferenceCrudController::PREF_PRO_ENGIN_ANNEE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_PUISSANCE])) {
            $tabAttributs[] = TextField::new('puissance', PreferenceCrudController::PREF_PRO_ENGIN_PUISSANCE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_VALEUR])) {
            $tabAttributs[] = MoneyField::new('valeur', PreferenceCrudController::PREF_PRO_ENGIN_VALEUR)
                ->formatValue(function ($value, Automobile $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getValeur());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_NB_SIEGES])) {
            $tabAttributs[] = NumberField::new('nbsieges', PreferenceCrudController::PREF_PRO_ENGIN_NB_SIEGES)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_USAGE])) {
            $tabAttributs[] = ChoiceField::new('utilite', PreferenceCrudController::PREF_PRO_ENGIN_USAGE)
                ->onlyOnIndex()
                ->setChoices(AutomobileCrudController::TAB_AUTO_UTILITE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_NATURE])) {
            $tabAttributs[] = ChoiceField::new('nature', PreferenceCrudController::PREF_PRO_ENGIN_NATURE)
                ->onlyOnIndex()
                ->setChoices(AutomobileCrudController::TAB_AUTO_NATURE);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ENGIN_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_ENGIN_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }

        return $tabAttributs;
    }

    public function setCRM_Fields_Engins_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ENGIN_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('plaque', PreferenceCrudController::PREF_PRO_ENGIN_N°_PLAQUE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_PRO_ENGIN_POLICE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('chassis', PreferenceCrudController::PREF_PRO_ENGIN_N°_CHASSIS)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('model', PreferenceCrudController::PREF_PRO_ENGIN_MODEL)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('marque', PreferenceCrudController::PREF_PRO_ENGIN_MARQUE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('annee', PreferenceCrudController::PREF_PRO_ENGIN_ANNEE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('puissance', PreferenceCrudController::PREF_PRO_ENGIN_PUISSANCE)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('valeur', PreferenceCrudController::PREF_PRO_ENGIN_VALEUR)
            ->formatValue(function ($value, Automobile $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getValeur());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = NumberField::new('nbsieges', PreferenceCrudController::PREF_PRO_ENGIN_NB_SIEGES)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('utilite', PreferenceCrudController::PREF_PRO_ENGIN_USAGE)
            ->onlyOnDetail()
            ->setChoices(AutomobileCrudController::TAB_AUTO_UTILITE);
        $tabAttributs[] = ChoiceField::new('nature', PreferenceCrudController::PREF_PRO_ENGIN_NATURE)
            ->onlyOnDetail()
            ->setChoices(AutomobileCrudController::TAB_AUTO_NATURE);
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ENGIN_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_ENGIN_ENTREPRISE)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ENGIN_DATE_DE_MODIFICATION)
            ->onlyOnDetail();

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

    public function setCRM_Fields_Assureur_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ASSUREUR_ID)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_ASSUREUR_NOM)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_COTATIONS])) {
            $tabAttributs[] = AssociationField::new('cotations', PreferenceCrudController::PREF_PRO_ASSUREUR_COTATIONS)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_POLICES])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_PRO_ASSUREUR_POLICES)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR])) {
            $tabAttributs[] = ChoiceField::new('isreassureur', PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR)
                ->onlyOnIndex()
                ->setChoices([
                    'Réassureur' => 1,
                    'Assureur' => 0
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM])) {
            $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_LICENCE])) {
            $tabAttributs[] = TextField::new('licence', PreferenceCrudController::PREF_PRO_ASSUREUR_LICENCE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_IDNAT])) {
            $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_ASSUREUR_IDNAT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_NUM_IMPOT])) {
            $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_ASSUREUR_NUM_IMPOT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ASSUREUR_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_ASSUREUR_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_Index(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

        return $tabAttributs;
    }

    public function setCRM_Fields_Assureur_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ASSUREUR_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_ASSUREUR_NOM)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('cotations', PreferenceCrudController::PREF_PRO_ASSUREUR_COTATIONS)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('police', PreferenceCrudController::PREF_PRO_ASSUREUR_POLICES)->onlyOnDetail();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE)->onlyOnDetail();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE)->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('isreassureur', PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR)
            ->onlyOnDetail()
            ->setChoices([
                'Réassureur' => 1,
                'Assureur' => 0
            ]);
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB)->onlyOnDetail();
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM)->onlyOnDetail();
        $tabAttributs[] = TextField::new('licence', PreferenceCrudController::PREF_PRO_ASSUREUR_LICENCE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_ASSUREUR_IDNAT)->onlyOnDetail();
        $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_ASSUREUR_NUM_IMPOT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ASSUREUR_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_ASSUREUR_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION)->onlyOnDetail();
        //LES CHAMPS CALCULABLES
        $tabAttributs = $this->setAttributs_Calculables_details(false, $tabAttributs);

        return $tabAttributs;
    }

    public function setAttributs_Calculables_Index($isPolice, array $tabAttributs, array $tabPreferences, array $tabIndiceAttribut)
    {
        //LES CHAMPS CALCULABLES
        if ($isPolice == false) {
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_primes_nette])) {
                $tabAttributs[] = MoneyField::new('calc_polices_primes_nette', PreferenceCrudController::PREF_calc_polices_primes_nette)
                    ->formatValue(function ($value, CalculableEntity $entity) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_primes_nette);
                    })
                    ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                    ->setStoredAsCents()
                    ->onlyOnIndex();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_fronting])) {
                $tabAttributs[] = MoneyField::new('calc_polices_fronting', PreferenceCrudController::PREF_calc_polices_fronting)
                    ->formatValue(function ($value, CalculableEntity $entity) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_fronting);
                    })
                    ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                    ->setStoredAsCents()
                    ->onlyOnIndex();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_accessoire])) {
                $tabAttributs[] = MoneyField::new('calc_polices_accessoire', PreferenceCrudController::PREF_calc_polices_accessoire)
                    ->formatValue(function ($value, CalculableEntity $entity) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_accessoire);
                    })
                    ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                    ->setStoredAsCents()
                    ->onlyOnIndex();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_tva])) {
                $tabAttributs[] = MoneyField::new('calc_polices_tva', PreferenceCrudController::PREF_calc_polices_tva)
                    ->formatValue(function ($value, CalculableEntity $entity) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_tva);
                    })
                    ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                    ->setStoredAsCents()
                    ->onlyOnIndex();
            }
            if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_polices_primes_totale])) {
                $tabAttributs[] = MoneyField::new('calc_polices_primes_totale', PreferenceCrudController::PREF_calc_polices_primes_totale)
                    ->formatValue(function ($value, CalculableEntity $entity) {
                        return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_primes_totale);
                    })
                    ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                    ->setStoredAsCents()
                    ->onlyOnIndex();
            }
        }

        //SINISTRE
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_sinistre_dommage_total])) {
            $tabAttributs[] = MoneyField::new('calc_sinistre_dommage_total', PreferenceCrudController::PREF_calc_sinistre_dommage_total)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_sinistre_dommage_total);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_sinistre_indemnisation_total])) {
            $tabAttributs[] = MoneyField::new('calc_sinistre_indemnisation_total', PreferenceCrudController::PREF_calc_sinistre_indemnisation_total)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_sinistre_indemnisation_total);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_sinistre_indice_SP])) {
            $tabAttributs[] = PercentField::new('calc_sinistre_indice_SP', PreferenceCrudController::PREF_calc_sinistre_indice_SP)
                ->setNumDecimals(2)
                ->onlyOnIndex();
        }

        //SECTION REVENU
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_reserve])) {
            $tabAttributs[] = MoneyField::new('calc_revenu_reserve', PreferenceCrudController::PREF_calc_revenu_reserve)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_reserve);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_partageable])) {
            $tabAttributs[] = MoneyField::new('calc_revenu_partageable', PreferenceCrudController::PREF_calc_revenu_partageable)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_partageable);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ht])) {
            $tabAttributs[] = MoneyField::new('calc_revenu_ht', PreferenceCrudController::PREF_calc_revenu_ht)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ht);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ttc])) {
            $tabAttributs[] = MoneyField::new('calc_revenu_ttc', PreferenceCrudController::PREF_calc_revenu_ttc)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ttc);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ttc_encaisse])) {
            $tabAttributs[] = MoneyField::new('calc_revenu_ttc_encaisse', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ttc_encaisse);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du])) {
            $tabAttributs[] = MoneyField::new('calc_revenu_ttc_solde_restant_du', PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ttc_solde_restant_du);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        //SECTION PARTENAIRES
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_retrocom])) {
            $tabAttributs[] = MoneyField::new('calc_retrocom', PreferenceCrudController::PREF_calc_retrocom)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_retrocom);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_retrocom_payees])) {
            $tabAttributs[] = MoneyField::new('calc_retrocom_payees', PreferenceCrudController::PREF_calc_retrocom_payees)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_retrocom_payees);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_retrocom_solde])) {
            $tabAttributs[] = MoneyField::new('calc_retrocom_solde', PreferenceCrudController::PREF_calc_retrocom_solde)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_retrocom_solde);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        //SECTION - TAXES
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_courtier_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Desc", PreferenceCrudController::PREF_calc_taxes_courtier_tab)) //
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier])) {
            $tabAttributs[] = MoneyField::new('calc_taxes_courtier', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_courtier))
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_courtier);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_payees])) {
            $tabAttributs[] = MoneyField::new('calc_taxes_courtier_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Pymnt", PreferenceCrudController::PREF_calc_taxes_courtier_payees))
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_courtier_payees);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_solde])) {
            $tabAttributs[] = MoneyField::new('calc_taxes_courtier_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Solde", PreferenceCrudController::PREF_calc_taxes_courtier_solde))
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_courtier_solde);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        //SECTION - TAXES
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Desc", PreferenceCrudController::PREF_calc_taxes_assureurs_tab))
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs])) {
            $tabAttributs[] = MoneyField::new('calc_taxes_assureurs', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_assureurs))
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_assureurs);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_payees])) {
            $tabAttributs[] = MoneyField::new('calc_taxes_assureurs_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Pymnt", PreferenceCrudController::PREF_calc_taxes_assureurs_payees))
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_assureurs_payees);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_solde])) {
            $tabAttributs[] = MoneyField::new('calc_taxes_assureurs_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Solde", PreferenceCrudController::PREF_calc_taxes_assureurs_solde))
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_assureurs_solde);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setAttributs_Calculables_details($isPolice, array $tabAttributs)
    {
        //LES CHAMPS CALCULABLES
        $tabAttributs[] = FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')->onlyOnDetail();
        if ($isPolice == false) {
            $tabAttributs[] = FormField::addPanel('Primes')
                ->setIcon('fa-solid fa-toggle-off')
                ->onlyOnDetail();
            $tabAttributs[] = MoneyField::new('calc_polices_primes_nette', PreferenceCrudController::PREF_calc_polices_primes_nette)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_primes_nette);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
            $tabAttributs[] = MoneyField::new('calc_polices_fronting', PreferenceCrudController::PREF_calc_polices_fronting)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_fronting);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
            $tabAttributs[] = MoneyField::new('calc_polices_accessoire', PreferenceCrudController::PREF_calc_polices_accessoire)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_accessoire);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
            $tabAttributs[] = MoneyField::new('calc_polices_tva', PreferenceCrudController::PREF_calc_polices_tva)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_tva);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
            $tabAttributs[] = MoneyField::new('calc_polices_primes_totale', PreferenceCrudController::PREF_calc_polices_primes_totale)
                ->formatValue(function ($value, CalculableEntity $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_polices_primes_totale);
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnDetail();
        }

        //SINISTRE
        $tabAttributs[] = FormField::addPanel('Sinistre')
            ->setIcon('fa-solid fa-toggle-off')
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_sinistre_dommage_total', PreferenceCrudController::PREF_calc_sinistre_dommage_total)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_sinistre_dommage_total);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_sinistre_indemnisation_total', PreferenceCrudController::PREF_calc_sinistre_indemnisation_total)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_sinistre_indemnisation_total);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = PercentField::new('calc_sinistre_indice_SP', PreferenceCrudController::PREF_calc_sinistre_indice_SP)
            ->onlyOnDetail();

        //SECTION REVENU
        $tabAttributs[] = FormField::addPanel('Commissions')
            ->setIcon('fa-solid fa-toggle-off')
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_revenu_reserve', PreferenceCrudController::PREF_calc_revenu_reserve)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_reserve);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_revenu_partageable', PreferenceCrudController::PREF_calc_revenu_partageable)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_partageable);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_revenu_ht', PreferenceCrudController::PREF_calc_revenu_ht)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ht);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_revenu_ttc', PreferenceCrudController::PREF_calc_revenu_ttc)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ttc);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_revenu_ttc_encaisse', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ttc_encaisse);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_revenu_ttc_solde_restant_du', PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_revenu_ttc_solde_restant_du);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        //SECTION PARTENAIRES
        $tabAttributs[] = FormField::addPanel('Retrocommossions')
            ->setIcon('fa-solid fa-toggle-off')
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_retrocom', PreferenceCrudController::PREF_calc_retrocom)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_retrocom);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_retrocom_payees', PreferenceCrudController::PREF_calc_retrocom_payees)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_retrocom_payees);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_retrocom_solde', PreferenceCrudController::PREF_calc_retrocom_solde)
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_retrocom_solde);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        //SECTION - TAXES
        $tabAttributs[] = FormField::addPanel('Impôts et Taxes')
            ->setIcon('fa-solid fa-toggle-off')
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('calc_taxes_courtier_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Desc", PreferenceCrudController::PREF_calc_taxes_courtier_tab)) //
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_taxes_courtier', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_courtier))
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_courtier);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_taxes_courtier_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Pymnt", PreferenceCrudController::PREF_calc_taxes_courtier_payees))
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_courtier_payees);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_taxes_courtier_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Solde", PreferenceCrudController::PREF_calc_taxes_courtier_solde))
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_courtier_solde);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        //SECTION - TAXES
        $tabAttributs[] = FormField::addPanel()->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Desc", PreferenceCrudController::PREF_calc_taxes_assureurs_tab))
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_taxes_assureurs', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_assureurs))
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_assureurs);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_taxes_assureurs_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Pymnt", PreferenceCrudController::PREF_calc_taxes_assureurs_payees))
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_assureurs_payees);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('calc_taxes_assureurs_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Solde", PreferenceCrudController::PREF_calc_taxes_assureurs_solde))
            ->formatValue(function ($value, CalculableEntity $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->calc_taxes_assureurs_solde);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        return $tabAttributs;
    }

    public function setAttributs_CalculablesTaxes_Index(array $tabAttributs, array $tabPreferences, array $tabIndiceAttribut)
    {
        //LES CHAMPS CALCULABLES
        //SECTION - TAXES - COURTIER
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_courtier))
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Pymnt", PreferenceCrudController::PREF_calc_taxes_courtier_payees))
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_courtier_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Solde", PreferenceCrudController::PREF_calc_taxes_courtier_solde))
                ->onlyOnIndex();
        }
        //SECTION - TAXES ASSUREURS
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_assureurs))
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Pymnt", PreferenceCrudController::PREF_calc_taxes_assureurs_payees))
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabIndiceAttribut[PreferenceCrudController::PREF_calc_taxes_assureurs_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Solde", PreferenceCrudController::PREF_calc_taxes_assureurs_solde))
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setAttributs_CalculablesTaxes_Details(array $tabAttributs)
    {
        //LES CHAMPS CALCULABLES
        $tabAttributs[] = FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')
            ->onlyOnDetail();
        //SECTION - TAXES - COURTIER
        $tabAttributs[] = FormField::addPanel('Impôts et Taxes')->setIcon('fa-solid fa-toggle-off')
            ->onlyOnDetail();
        $tabAttributs[] = NumberField::new('calc_taxes_courtier', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_courtier))
            ->onlyOnDetail();
        $tabAttributs[] = NumberField::new('calc_taxes_courtier_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Pymnt", PreferenceCrudController::PREF_calc_taxes_courtier_payees))
            ->onlyOnDetail();
        $tabAttributs[] = NumberField::new('calc_taxes_courtier_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Solde", PreferenceCrudController::PREF_calc_taxes_courtier_solde))
            ->onlyOnDetail();
        //SECTION - TAXES ASSUREURS
        $tabAttributs[] = FormField::addPanel()
            ->onlyOnDetail();
        $tabAttributs[] = NumberField::new('calc_taxes_assureurs', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_assureurs))
            ->onlyOnDetail();
        $tabAttributs[] = NumberField::new('calc_taxes_assureurs_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Pymnt", PreferenceCrudController::PREF_calc_taxes_assureurs_payees))
            ->onlyOnDetail();
        $tabAttributs[] = NumberField::new('calc_taxes_assureurs_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Solde", PreferenceCrudController::PREF_calc_taxes_assureurs_solde))
            ->onlyOnDetail();
        return $tabAttributs;
    }

    public function canHide($adminUrlGenerator, $nomChamp): Bool
    {
        $reponse = false;
        $champsACacher = new ArrayCollection([]);
        if ($adminUrlGenerator->get("champsACacher")) {
            $champsACacher = new ArrayCollection($adminUrlGenerator->get("champsACacher"));
        }
        //dd($champsACacher);
        $reponse = !$champsACacher->contains($nomChamp);
        //dd($champsACacher . ": " . $reponse);

        return $reponse;
    }

    public function canDesable($adminUrlGenerator, $nomChamp): Bool
    {
        $reponse = false;
        $champsADesactiver = new ArrayCollection([]);
        if ($adminUrlGenerator->get("champsADesactiver")) {
            $champsADesactiver = new ArrayCollection($adminUrlGenerator->get("champsADesactiver"));
        }
        //dd($champsACacher);
        $reponse = $champsADesactiver->contains($nomChamp);
        //dd($champsACacher . ": " . $reponse);
        return $reponse;
    }

    public function setCRM_Fields_Cotation_form($tabAttributs, $adminUrlGenerator)
    {
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_NOM)) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)
                ->onlyOnForms()
                ->setRequired(false)
                //->setColumns(6);
                ->setColumns(12);
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)
                ->setRequired(false)
                //->setColumns(6)
                ->setColumns(12)
                ->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_RESULTAT)) {
            $tabAttributs[] = BooleanField::new('validated', PreferenceCrudController::PREF_CRM_COTATION_RESULTAT)
                ->setColumns(12)
                ->renderAsSwitch(true) //il reste éditable
                ->setRequired(true)
                ->onlyOnForms();
            //->setChoices(CotationCrudController::TAB_TYPE_RESULTAT);
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_DUREE)) {
            $tabAttributs[] = NumberField::new('dureeCouverture', PreferenceCrudController::PREF_CRM_COTATION_DUREE)
                ->setColumns(12)
                ->onlyOnForms();
            //->setChoices(CotationCrudController::TAB_TYPE_RESULTAT);
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_CHARGEMENT)) {
            $tabAttributs[] = FormField::addPanel("Détails relatifs à la prime d'assurance")
                ->onlyOnForms();
            $tabAttributs[] = CollectionField::new('chargements', PreferenceCrudController::PREF_CRM_COTATION_CHARGEMENT)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                ->useEntryCrudForm(ChargementCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setEntryIsComplex()
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms();
            $tabAttributs[] = MoneyField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                //->setColumns(4);
                ->setColumns(12);
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_TRANCHES)) {
            $tabAttributs[] = FormField::addPanel("Détails relatifs aux termes de paiement.")
                ->onlyOnForms();
            $tabAttributs[] = CollectionField::new('tranches', PreferenceCrudController::PREF_CRM_COTATION_TRANCHES)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                ->useEntryCrudForm(TrancheCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setEntryIsComplex()
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms();
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_REVENUS)) {
            $tabAttributs[] = FormField::addPanel("Détails relatifs à la commission de courtage")
                ->onlyOnForms();
            $tabAttributs[] = CollectionField::new('revenus', PreferenceCrudController::PREF_CRM_COTATION_REVENUS)
                ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                ->useEntryCrudForm(RevenuCrudController::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setEntryIsComplex()
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms();
            $tabAttributs[] = MoneyField::new('revenuTotalHT', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setHelp("La partie partageable + la partie non partageable.")
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);
            $tabAttributs[] = MoneyField::new('taxeCourtierTotale', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);
            $tabAttributs[] = MoneyField::new('revenuNetTotal', "Revenu net total")
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);

            $tabAttributs[] = FormField::addPanel("Détails relatifs à la retrocommission dûe au partenaire")
                ->onlyOnForms();
            $tabAttributs[] = MoneyField::new('revenuTotalHTPartageable', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setHelp("Uniquement la partie partageable avec le partenaire.")
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);

            $tabAttributs[] = MoneyField::new('taxeCourtierTotalePartageable', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);
            $tabAttributs[] = MoneyField::new('revenuNetTotalPartageable', "Revenu net partageable")
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setHelp("La partie du revenu net qui est parteageable avec le partenaire ou encore l'assiette.")
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);
            $tabAttributs[] = TextField::new('partenaire', "Partenaire")
                //->setHelp("")
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);
            $tabAttributs[] = PercentField::new('tauxretrocompartenaire', PreferenceCrudController::PREF_CRM_COTATION_TAUX_RETROCOM)
                ->setColumns(12)
                ->setHelp("Ne définissez rien si vous voullez appliquer le taux par défaut.")
                ->setNumDecimals(2)
                ->onlyOnForms();
            $tabAttributs[] = MoneyField::new('retroComPartenaire', "Retrocommission")
                ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                ->setHelp("Le montant total dû au partenaire.")
                ->setStoredAsCents()
                ->onlyOnForms()
                ->setDisabled(true)
                ->setColumns(12);
        }
        // if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_REVENU_TOTAL_HT)) {
        // }

        return $tabAttributs;
    }

    public function setCRM_Fields_Cotation_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_COTATION_ID)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_RESULTAT])) {
            $tabAttributs[] = ChoiceField::new('validated', PreferenceCrudController::PREF_CRM_COTATION_RESULTAT)
                ->onlyOnIndex()
                ->setChoices(CotationCrudController::TAB_TYPE_RESULTAT)
                ->renderAsBadges([
                    CotationCrudController::TAB_TYPE_RESULTAT[CotationCrudController::TYPE_RESULTAT_VALIDE] => 'success',
                    CotationCrudController::TAB_TYPE_RESULTAT[CotationCrudController::TYPE_RESULTAT_NON_VALIDEE] => 'dark',
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)->onlyOnIndex();
        }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_PRIME_NETTE])) {
        //     $tabAttributs[] = MoneyField::new('primeNette', PreferenceCrudController::PREF_CRM_COTATION_PRIME_NETTE)
        //         ->formatValue(function ($value, Cotation $entity) {
        //             return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeNette());
        //         })
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setStoredAsCents()
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ACCESSOIRES])) {
        //     $tabAttributs[] = MoneyField::new('accessoires', PreferenceCrudController::PREF_CRM_COTATION_ACCESSOIRES)
        //         ->formatValue(function ($value, Cotation $entity) {
        //             return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getAccessoires());
        //         })
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setStoredAsCents()
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_TAXE])) {
        //     $tabAttributs[] = MoneyField::new('taxes', PreferenceCrudController::PREF_CRM_COTATION_TAXE)
        //         ->formatValue(function ($value, Cotation $entity) {
        //             return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxes());
        //         })
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setStoredAsCents()
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ARCA])) {
        //     $tabAttributs[] = MoneyField::new('fraisArca', PreferenceCrudController::PREF_CRM_COTATION_ARCA)
        //         ->formatValue(function ($value, Cotation $entity) {
        //             return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFraisArca());
        //         })
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setStoredAsCents()
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE])) {
        //     $tabAttributs[] = MoneyField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE)
        //         ->formatValue(function ($value, Cotation $entity) {
        //             return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeTotale());
        //         })
        //         ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //         ->setStoredAsCents()
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_PRODUIT])) {
        //     $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_COTATION_PRODUIT)->onlyOnIndex();
        // }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_COTATION_PISTE)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR])) {
            $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION)->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Cotation_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_COTATION_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('chargements', PreferenceCrudController::PREF_CRM_COTATION_CHARGEMENT)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('revenus', PreferenceCrudController::PREF_CRM_COTATION_REVENUS)->onlyOnDetail();

        // $tabAttributs[] = MoneyField::new('primeNette', PreferenceCrudController::PREF_CRM_COTATION_PRIME_NETTE)
        //     ->formatValue(function ($value, Cotation $entity) {
        //         return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeNette());
        //     })
        //     ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //     ->setStoredAsCents()
        //     ->onlyOnDetail();
        // $tabAttributs[] = MoneyField::new('accessoires', PreferenceCrudController::PREF_CRM_COTATION_ACCESSOIRES)
        //     ->formatValue(function ($value, Cotation $entity) {
        //         return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getAccessoires());
        //     })
        //     ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //     ->setStoredAsCents()
        //     ->onlyOnDetail();
        // $tabAttributs[] = MoneyField::new('fronting', PreferenceCrudController::PREF_CRM_COTATION_FRONTING)
        //     ->formatValue(function ($value, Cotation $entity) {
        //         return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFronting());
        //     })
        //     ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //     ->setStoredAsCents()
        //     ->onlyOnDetail();
        // $tabAttributs[] = MoneyField::new('fraisArca', PreferenceCrudController::PREF_CRM_COTATION_ARCA)
        //     ->formatValue(function ($value, Cotation $entity) {
        //         return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getFraisArca());
        //     })
        //     ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //     ->setStoredAsCents()
        //     ->onlyOnDetail();
        // $tabAttributs[] = MoneyField::new('taxes', PreferenceCrudController::PREF_CRM_COTATION_TAXE)
        //     ->formatValue(function ($value, Cotation $entity) {
        //         return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxes());
        //     })
        //     ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //     ->setStoredAsCents()
        //     ->onlyOnDetail();
        // $tabAttributs[] = MoneyField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE)
        //     ->formatValue(function ($value, Cotation $entity) {
        //         return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeTotale());
        //     })
        //     ->setCurrency($this->serviceMonnaie->getCodeAffichage())
        //     ->setStoredAsCents()
        //     ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('validated', PreferenceCrudController::PREF_CRM_COTATION_RESULTAT)
            ->onlyOnDetail()
            ->setChoices(CotationCrudController::TAB_TYPE_RESULTAT);
        //$tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_COTATION_PRODUIT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_COTATION_PISTE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->hideOnForm();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION)->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_FEEDBACK_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE])) {
            $tabAttributs[] = TextField::new('Message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE])) {
            $tabAttributs[] = TextField::new('prochaineTache', PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION])) {
            $tabAttributs[] = AssociationField::new('action', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET])) {
            $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_FEEDBACK_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('Message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('prochaineTache', PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('action', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_form($tabAttributs)
    {
        $tabAttributs[] = TextEditorField::new('message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)->onlyOnForms()->setColumns(6);
        $tabAttributs[] = TextEditorField::new('prochaineTache', PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE)->onlyOnForms()->setColumns(6);
        if ($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)) {
            $tabAttributs[] = AssociationField::new('action', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)
                ->onlyOnForms()
                ->setColumns(6)
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET)->onlyOnForms()->setColumns(6);
        return $tabAttributs;
    }

    public function setCRM_Fields_Etapes_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)
            ->onlyOnForms()
            ->setColumns(6);

        return $tabAttributs;
    }

    public function setCRM_Fields_Action_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_MISSION_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)->onlyOnDetail();
        $tabAttributs[] = TextareaField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
            ->renderAsHtml()
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('clos', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
            ->onlyOnDetail()
            ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
            ->setChoices(ActionCRMCrudController::STATUS_MISSION)
            ->renderAsBadges([
                ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ACHEVEE] => 'success', //info
                ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS] => 'warning',
            ]);
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_MISSION_POLICE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_CRM_MISSION_COTATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('feedbackCRMs', PreferenceCrudController::PREF_CRM_MISSION_FEEDBACKS)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT)->onlyOnDetail();
        return $tabAttributs;
    }

    public function setCRM_Fields_Action_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_MISSION_ID)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_NOM])) {
            $tabAttributs[] = TextField::new('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF])) {
            $tabAttributs[] = TextEditorField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_STATUS])) {
            $tabAttributs[] = ChoiceField::new('clos', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
                ->onlyOnIndex()
                ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
                ->setChoices(ActionCRMCrudController::STATUS_MISSION)
                ->renderAsBadges([
                    ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ACHEVEE] => 'success', //info
                    ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS] => 'warning',
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_POLICE])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_MISSION_POLICE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_COTATION])) {
            $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_CRM_MISSION_COTATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_SINISTRE])) {
            $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_PISTE])) {
            $tabAttributs[] = AssociationField::new('feedbackCRMs', PreferenceCrudController::PREF_CRM_MISSION_FEEDBACKS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT])) {
            $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT])) {
            $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A])) {
            $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Action_form($tabAttributs, $adminUrlGenerator)
    {
        $tabAttributs[] = TextField::new('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
            ->setRequired(false)
            ->onlyOnForms()
            ->setColumns(12)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_MISSION_POLICE)) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_MISSION_POLICE)
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_MISSION_COTATION)) {
            $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_CRM_MISSION_COTATION)
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)) {
            $tabAttributs[] = AssociationField::new('sinistre', PreferenceCrudController::PREF_CRM_MISSION_SINISTRE)
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        $tabAttributs[] = TextEditorField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
            //->setFormType(CKEditorType::class)
            ->onlyOnForms()
            ->setColumns(12);
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_MISSION_STATUS)) {
            $tabAttributs[] = ChoiceField::new('clos', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
                ->onlyOnForms()
                ->setColumns(12)
                ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
                ->setChoices(ActionCRMCrudController::STATUS_MISSION);
        }
        if ($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_MISSION_PISTE)) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)
                ->setRequired(false)
                ->setColumns(12)
                ->onlyOnForms()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                });
        }
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
            ->onlyOnForms()
            ->setColumns(12);
        return $tabAttributs;
    }


    public function setCRM_Fields_Etapes_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_ETAPES_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('pistes', PreferenceCrudController::PREF_CRM_ETAPES_PISTES)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION)->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_Etapes_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_ETAPES_ID)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_PISTES])) {
            $tabAttributs[] = AssociationField::new('pistes', PreferenceCrudController::PREF_CRM_ETAPES_PISTES)->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR)
                ->onlyOnIndex()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }


    public function setCRM_Fields_Pistes_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_PISTE_ID)
                ->onlyOnIndex();
        }
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
            ->formatValue(function ($value, Piste $piste) {
                $this->setTitreReportingCRM($piste);
                return $value;
            })
            ->onlyOnIndex(); //->setColumns(6);
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT])) {
            $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
                ->onlyOnIndex()
                ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT)
                ->renderAsBadges([
                    // $value => $badgeStyleName
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_ANNULATION] => 'dark', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_SOUSCRIPTION] => 'success', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_INCORPORATION] => 'info', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_PROROGATION] => 'success', //info
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RENOUVELLEMENT] => 'success',
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RESILIATION] => 'warning',
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_RISTOURNE] => 'danger',
                    PoliceCrudController::TAB_POLICE_TYPE_AVENANT[PoliceCrudController::AVENANT_TYPE_AUTRE_MODIFICATION] => 'info'
                ]);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_CLIENT])) {
            $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE])) {
            $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_PRODUIT])) {
            $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_POLICE])) {
            $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
                ->onlyOnIndex();
        }


        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF])) {
            $tabAttributs[] = TextareaField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
                ->renderAsHtml()
                ->onlyOnIndex(); //->setColumns(6);
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_MONTANT])) {
            $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
                ->formatValue(function ($value, Piste $entity) {
                    return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
                })
                ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                ->setStoredAsCents()
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_CONTACT])) {
            $tabAttributs[] = AssociationField::new('contacts', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_ACTIONS])) {
            $tabAttributs[] = AssociationField::new('actionsCRMs', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_COTATION])) {
            $tabAttributs[] = AssociationField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_ETAPE])) {
            $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION])) {
            $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR)
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION)
                ->onlyOnIndex();
        }
        if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Pistes_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_PISTE_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
            ->formatValue(function ($value, Piste $piste) {
                $this->setTitreReportingCRM($piste);
                return $value;
            })
            ->onlyOnDetail(); //->setColumns(6);
        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
            ->onlyOnDetail()
            ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT);
        $tabAttributs[] = TextareaField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
            ->renderAsHtml()
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
            ->formatValue(function ($value, Piste $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)->onlyOnDetail();

        //Onglet Contacts
        $tabAttributs[] = FormField::addTab(' Contacts')
            ->setIcon('fas fa-address-book')
            ->setHelp("Les contacts impliqués dans les échanges pour cette piste.")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('contacts', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)->onlyOnDetail();

        //Onglet Cotations
        $tabAttributs[] = FormField::addTab(' Missions')
            ->setIcon('fas fa-paper-plane')
            ->setHelp("Les missions ou actions à exécuter qui ont été assignées aux utilisateur pour cette piste.")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('actionsCRMs', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)->onlyOnDetail();



        //Onglet Cotations
        $tabAttributs[] = FormField::addTab(' Propositions')
            ->setIcon('fas fa-cash-register')
            ->setHelp("Les propositions ou cotations produites dans le cadre de cette piste.")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)->onlyOnDetail();

        //Onglet Cotations
        $tabAttributs[] = FormField::addTab(' Polices')
            ->setIcon('fas fa-file-shield')
            ->setHelp("Les polices produites et placées à partir de cette piste.")
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_PISTE_POLICE)->onlyOnDetail();


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
            ->setColumns(3);

        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setColumns(2);

        $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
            ->setColumns(2)
            ->onlyOnForms()
            ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT);

        $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
            ->onlyOnForms()
            ->setColumns(3)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });

        $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
            ->onlyOnForms()
            ->setColumns(2);

        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
            ->onlyOnForms()
            ->setColumns(6)
            ->setRequired(false)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });

        $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
            ->onlyOnForms()
            ->setColumns(6)
            ->setRequired(true)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });

        $tabAttributs[] = TextEditorField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
            ->onlyOnForms()
            ->setColumns(12);

        //Onglet Partenaire
        $tabAttributs[] = FormField::addTab(' Partenaire')
            ->setIcon('fas fa-handshake')
            ->setHelp("Intermédiaire (parrain) à travers lequel vous êtes entrés en contact avec cette piste.")
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE)
            ->setHelp("Si le partenaire n'existe pas encore sur cette liste, vous pouvez l'ajouter. Pour cela, il faut allez sur le champ d'ajout du partenaire.")
            ->onlyOnForms()
            ->setColumns(5)
            ->setRequired(false)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = CollectionField::new('newpartenaire', PreferenceCrudController::PREF_CRM_PISTE_NEW_PARTENAIRE)
            ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté, mais seul la première sera finalement prise en compte.")
            ->useEntryCrudForm(PartenaireCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(7)
            ->onlyOnForms();

        //Onglet Client ou Prospect
        $tabAttributs[] = FormField::addTab(' Client')
            ->setIcon('fas fa-person-shelter')
            ->setHelp("Le client ou prospect concerné par cette piste.")
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
            ->setHelp("Si le client n'existe pas encore sur cette liste, vous pouvez l'ajouter comme prospect. Pour cela, il faut allez sur le champ d'ajout de prospect.")
            ->onlyOnForms()
            ->setColumns(5)
            ->setRequired(false)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });

        $tabAttributs[] = CollectionField::new('prospect', PreferenceCrudController::PREF_CRM_PISTE_PROSPECTS)
            ->setHelp("Vous avez la possibilité d'ajouter des données à volonté. Mais seul le premier sera pris en compte.")
            ->useEntryCrudForm(ClientCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(7)
            ->onlyOnForms();

        //Onglet Contacts
        $tabAttributs[] = FormField::addTab(' Contacts')
            ->setIcon('fas fa-address-book')
            ->setHelp("Les contacts impliqués dans les échanges pour cette piste.")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('contacts', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
            ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(ContactCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(7)
            ->onlyOnForms();

        //Onglet Cotations
        $tabAttributs[] = FormField::addTab(' Missions')
            ->setIcon('fas fa-paper-plane')
            ->setHelp("Les missions ou actions à exécuter qui ont été assignées aux utilisateur pour cette piste.")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('actionsCRMs', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
            ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(ActionCRMCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(7)
            ->onlyOnForms();

        //Onglet Cotations
        $tabAttributs[] = FormField::addTab(' Propositions')
            ->setIcon('fas fa-cash-register')
            ->setHelp("Offres de proposition pour le client / prospect.")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
            ->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(CotationCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(7)
            ->onlyOnForms();


        //dd($tabAttributs);
        return $tabAttributs;
    }

    public function getChamps($objetInstance, ?Crud $crud, AdminUrlGenerator $adminUrlGenerator)
    {
        //définition des attributs des pages
        $preference = $this->chargerPreference($this->serviceEntreprise->getUtilisateur(), $this->serviceEntreprise->getEntreprise());

        return $this->definirAttributsPages($objetInstance, $preference, $crud, $adminUrlGenerator);
    }

    public function setDefaultData(Preference $preference)
    {
        $preference->setApparence(0);
        $preference->setCreatedAt(new DateTimeImmutable());
        $preference->setUpdatedAt(new DateTimeImmutable());
        //CRM
        $preference->setCrmTaille(100);
        $preference->setCrmMissions([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]);
        $preference->setCrmFeedbacks([1, 2, 3, 4, 5, 6, 7, 8]);
        $preference->setCrmCotations([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]);
        $preference->setCrmEtapes([1, 2, 3, 4, 5, 6]);
        $preference->setCrmPistes([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
        //PRO
        $preference->setProTaille(100);
        $preference->setProAssureurs([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41]);
        $preference->setProAutomobiles([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]);
        $preference->setProContacts([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $preference->setProClients([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41]);
        $preference->setProPartenaires([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39]);
        $preference->setProPolices([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65]);
        $preference->setProProduits([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38]);
        //FIN
        $preference->setFinTaille(100);
        $preference->setFinTaxes([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]);
        $preference->setFinMonnaies([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $preference->setFinCommissionsPayees([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $preference->setFinRetrocommissionsPayees([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
        $preference->setFinTaxesPayees([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
        $preference->setFinFactures([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16]);
        $preference->setFinElementFactures([1, 2, 3, 4, 5, 6, 7]);
        //SIN
        $preference->setSinTaille(100);
        $preference->setSinEtapes([1, 2, 3, 4, 5, 6, 7, 8]);
        $preference->setSinExperts([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
        $preference->setSinSinistres([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42]);
        $preference->setSinVictimes([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        //BIB
        $preference->setBibTaille(100);
        $preference->setBibCategories([1, 2, 3, 4, 5, 6]);
        $preference->setBibClasseurs([1, 2, 3, 4, 5, 6]);
        $preference->setBibPieces([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]);
        //PAR
        $preference->setParTaille(100);
        $preference->setParUtilisateurs([1, 2, 3, 4, 5, 6, 7, 8, 9]);

        return $preference;
    }

    public function getDefaultPreferences($utilisateur, $entreprise)
    {
        $preference = new Preference();
        $preference->setUtilisateur($utilisateur);
        $preference->setEntreprise($entreprise);
        $preference = $this->setDefaultData($preference);
        return $preference;
    }

    public function creerPreference($utilisateur, $entreprise)
    {
        //persistance
        $this->entityManager->persist($this->getDefaultPreferences($utilisateur, $entreprise));
        $this->entityManager->flush();
    }

    public function resetPreferences(Utilisateur $utilisateur, Entreprise $entreprise)
    {
        $preferences = $this->chargerPreference($utilisateur, $entreprise);
        $preferences = $this->setDefaultData($preferences);
        //dd($preferences);
        $this->entityManager->persist($preferences);
        $this->entityManager->flush();
    }

    public function setTitreReporting(Police $police)
    {
        if ($this->crud) {
            //dd($this->adminUrlGenerator->get("codeReporting"));
            if ($this->adminUrlGenerator->get("codeReporting") != null) {
                //COMMISSION
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_COM) {
                    $this->total_unpaidcommission += $police->getUnpaidcommission();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidcommission) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_COM) {
                    $this->total_paidcommission += $police->calc_revenu_ttc_encaisse;
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total encaissé: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidcommission) . "]");
                }
                //RETRO-COMMISSION
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_RETROCOM) {
                    $this->total_unpaidretrocommission += $police->getUnpaidretrocommission();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidretrocommission) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_RETROCOM) {
                    $this->total_paidretrocommission += $police->calc_retrocom_payees;
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidretrocommission) . "]");
                }
                //TAXES
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE) {
                    $this->total_unpaidtaxe += $police->getUnpaidtaxe();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidtaxe) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_TAXE) {
                    $this->total_paidtaxe += $police->getPaidtaxe();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidtaxe) . "]");
                }
                //TAXES COURTIERS
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE_COURTIER) {
                    $this->total_unpaidtaxecourtier += $police->getUnpaidtaxecourtier();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidtaxecourtier) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_TAXE_COURTIER) {
                    $this->total_paidtaxecourtier += $police->getPaidtaxecourtier();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidtaxecourtier) . "]");
                }
                //TAXES ASSUREUR
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE_ASSUREUR) {
                    $this->total_unpaidtaxeassureur += $police->getUnpaidtaxeassureur();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidtaxeassureur) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_TAXE_ASSUREUR) {
                    $this->total_paidtaxeassureur += $police->getPaidtaxeassureur();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidtaxeassureur) . "]");
                }
                //PRODUCTION GLOBALE
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PRODUCTION_TOUS) {
                    $this->total_prime_nette += $police->getPrimenette();
                    $this->total_prime_fronting += $police->getFronting();
                    $this->total_prime_accessoire += $police->getFraisadmin();
                    $this->total_prime_arca += $police->getArca();
                    $this->total_prime_tva += $police->getTva();
                    $this->total_prime_ttc += $police->getPrimetotale();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " \n
                    [
                        Prime totale: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_prime_ttc) . ", 
                        Prime nette: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_prime_nette) . ",
                        Fronting: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_prime_fronting) . ",
                        " . strtoupper($this->serviceTaxes->getNomTaxeAssureur()) . ": " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_prime_tva) . ",
                        " . strtoupper($this->serviceTaxes->getNomTaxeCourtier()) . ": " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_prime_arca) . ",
                        Accessoires: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_prime_accessoire) . ",
                    ]");
                }
            }
        }
    }

    public $tDu = 0;
    public $tRecu = 0;
    public $tSolde = 0;
    public $str_totaux = 0;

    public function setTotauxFacture(Facture $facture)
    {
        //dd($this->crud);
        if ($this->crud) {
            $this->tDu = $this->tDu + $facture->getTotalDu();
            $this->tRecu = $this->tRecu + $facture->getTotalRecu();
            $this->tSolde = $this->tSolde + $facture->getTotalSolde();

            $str_totaux = "[Total: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->tDu) . ", Payé: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->tRecu) . ", Solde: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->tSolde) . "]";

            $this->crud->setPageTitle(Crud::PAGE_INDEX, "Factures - " . $str_totaux);
        }
    }


    public $tEntrees = 0;
    public $tSorties = 0;

    public function setTotauxPaiement(Paiement $paiement)
    {
        //dd($this->crud);
        if ($this->crud) {
            if ($paiement->getType() == PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_ENTREE]) {
                $this->tEntrees = $this->tEntrees + $paiement->getMontant();
            }
            if ($paiement->getType() == PaiementCrudController::TAB_TYPE_PAIEMENT[PaiementCrudController::TYPE_PAIEMENT_SORTIE]) {
                $this->tSorties = $this->tSorties + $paiement->getMontant();
            }

            $str_totaux = "[Entrées: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->tEntrees) . ", Sortie: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->tSorties) . ", Solde: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage(($this->tEntrees - $this->tSorties)) . "]";
            $this->crud->setPageTitle(Crud::PAGE_INDEX, "Cashflow - " . $str_totaux);
        }
    }

    public function setTitreReportingCRM(Piste $piste)
    {
        //dd($this->adminUrlGenerator->get("codeReporting"));
        if ($this->adminUrlGenerator->get("codeReporting") != null) {
            //SINISTRE
            if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PISTE_TOUS) {
                $this->total_piste_caff_esperes += $piste->getMontant();
                $com_ttc = 0;
                $prime_ttc = 0;
                foreach ($piste->getCotations() as $cotation) {
                    /** @var Cotation */
                    $cota = $cotation;
                    // if ($cota->getPolice() != null) {
                    //     /** @var Police */
                    //     $pol = $cota->getPolice();
                    //     //On force le calcul des champs calculables
                    //     $this->serviceCalculateur->updatePoliceCalculableFileds($pol);
                    //     $prime_ttc += $pol->getPrimetotale();
                    //     $com_ttc += $pol->calc_revenu_ttc;
                    //     //dd($pol);
                    // }
                }

                if ($this->crud) {
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " \n
                    [
                        Revenus potentiels: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_piste_caff_esperes) . ",
                        Revenus générés: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($com_ttc) . ",
                        Primes générées: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($prime_ttc) . "
                    ]");
                }
            }
        }
    }

    public function setTitreReportingSinistre(Sinistre $sinistre)
    {
        //dd($this->adminUrlGenerator->get("codeReporting"));
        if ($this->adminUrlGenerator->get("codeReporting") != null) {
            //SINISTRE
            if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_SINISTRE_TOUS) {
                $this->total_sinistre_cout += $sinistre->getCout();
                $this->total_sinistre_indemnisation += $sinistre->getMontantPaye();

                if ($this->crud) {
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " \n
                    [
                        Dégâts estimés: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_sinistre_cout) . ", 
                        Compensation versée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_sinistre_indemnisation) . "
                    ]");
                }
            }
        }
    }
}
