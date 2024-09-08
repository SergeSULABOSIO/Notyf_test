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
use App\Controller\Admin\FeedbackCRMCrudController;
use App\Controller\Admin\PartenaireCrudController;
use App\Controller\Admin\PisteCrudController;
use App\Controller\Admin\RevenuCrudController;
use App\Controller\Admin\TrancheCrudController;
use App\Entity\Chargement;
use App\Entity\Revenu;
use App\Entity\Tranche;
use Doctrine\ORM\Query\Expr\Func;
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
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use SebastianBergmann\CodeCoverage\Util\Percentage;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

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

    private ?string $pageName = null;
    private $entityInstance = null;
    private $isNewPiste = false;
    private ?Piste $piste = null;


    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServiceCompteBancaire $serviceCompteBancaire,
        private ServiceTaxes $serviceTaxes,
        private ServiceMonnaie $serviceMonnaie,
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
                FormField::addPanel(' Informations générales')
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
                FormField::addPanel(' Informations générales')
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
                FormField::addPanel(' Informations générales')
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
                FormField::addPanel(' Informations de base')
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
                FormField::addPanel(' Informations générales')
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
                FormField::addTab(' Informations générales')
                    ->setIcon('fa-solid fa-receipt') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Facture / Note de débit / Note de percetion pertant de collecter des fonds. Ceci n'est qu'une représentation virtuelle de la copie réelle que vous devez attacher ici dès que possible.")
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
        // if ($objetInstance instanceof Paiement) {
        //     $tabAttributs = [
        //         FormField::addTab('Informations générales')
        //             ->setIcon('fa-solid fa-cash-register') //<i class="fa-sharp fa-solid fa-address-book"></i>
        //             ->setHelp("Veuillez saisir les informations relatives au paiement.")
        //     ];
        //     //$tabAttributs = $this->setCRM_Fields_PaiementTaxes_Index_Details($preference->getFinTaxesPayees(), PreferenceCrudController::TAB_FIN_PAIEMENTS_TAXES, $tabAttributs);
        //     $tabAttributs = $this->setFIN_Fields_Paiement_Index($preference->getFinTaxesPayees(), PreferenceCrudController::TAB_FIN_PAIEMENT, $tabAttributs);
        //     $tabAttributs = $this->setFIN_Fields_Paiement_Details($tabAttributs);
        //     $tabAttributs = $this->setFIN_Fields_Paiement_form($tabAttributs);
        // }
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
        $taux = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);
        //dd($taux);
        //$tabAttributs = [];
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_POLICE_ID)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)->onlyOnDetail();
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)->onlyOnDetail();
        $tabAttributs[] = TextField::new('assureur', "Assureur")->onlyOnDetail();
        $tabAttributs[] = TextField::new('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)->onlyOnDetail();
        $tabAttributs[] = TextField::new('produit', "Couverture")
            ->setTemplatePath('admin/segment/view_produit.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('client', "Assuré (client)")->onlyOnDetail();
        $tabAttributs[] = TextField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('assistant', PreferenceCrudController::PREF_PRO_POLICE_ASSISTANT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_POLICE_ENTREPRISE)->onlyOnDetail();

        //Onglet Contacts
        $tabAttributs[] = FormField::addPanel(" Détails relatifs aux Contacts")
            ->setIcon("fas fa-address-book")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('contacts', "Détails")
            ->setTemplatePath('admin/segment/view_contacts.html.twig')
            ->onlyOnDetail();

        //Onglet Documents
        $tabAttributs[] = FormField::addPanel(" Documents")
            ->setIcon("fa-solid fa-paperclip")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('documents', "Détails")
            ->setTemplatePath('admin/segment/view_documents.html.twig')
            ->onlyOnDetail();

        //Onglet Structure de la prime
        $tabAttributs[] = FormField::addPanel(" Détails relatifs à la prime d'assurance")
            ->setIcon("fa-solid fa-cash-register")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('chargements', "Détails")
            ->setTemplatePath('admin/segment/view_chargements.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();

        //Onglet Termes de paiements
        $tabAttributs[] = FormField::addPanel(" Détails relatifs aux termes de paiement")
            ->setIcon("fa-solid fa-cash-register")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('tranches', "Détails")
            ->setTemplatePath('admin/segment/view_tranches.html.twig')
            ->onlyOnDetail();

        //Onglet Revenu de courtage
        $tabAttributs[] = FormField::addPanel(" Détails relatifs à la commission de courtage")
            ->setIcon("fa-solid fa-cash-register")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('revenus', "Détails")
            ->setTemplatePath('admin/segment/view_revenus.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('commissionTotaleHT', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('taxeCourtierTotale', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($taux * 100) . "%)"))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('revenuNetTotal', "Revenu net total")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();

        //Onglet Rétrocommission
        $tabAttributs[] = FormField::addPanel(" Détails relatifs à la rétrocommission dûe au partenaire")
            ->setIcon("fas fa-handshake")
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('revenuTotalHTPartageable', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('taxeCourtierTotalePartageable', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($taux * 100) . "%)"))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('revenuNetTotalPartageable', "Revenu net partageable")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();
        //Je suis ici
        $tabAttributs[] = TextField::new('partenaire', "Partenaire")
            ->setDisabled(true)
            ->onlyOnDetail();
        $tabAttributs[] = PercentField::new('tauxretrocompartenaire', "Taux exceptionnel")
            ->setNumDecimals(2)
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('retroComPartenaire', "Rétrocommission")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnDetail();


        return $tabAttributs;
    }

    public function setFIN_Fields_Revenu_Details($tabAttributs)
    {
        $tabAttributs[] = BooleanField::new('validated', "Validée")
            ->renderAsSwitch(false)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_REVENU_TYPE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_TYPE);
        $tabAttributs[] = TextField::new('police', "Police")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('client', "Client")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('assureur', "Assureur")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('partenaire', "Partenaire")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('produit', "Produit")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateEffet', "Date d'effet")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateExpiration', "Date d'expiration")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateOperation', "Date d'opération")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateEmition', "Date d'émition")
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PROD_TRANCHE_COTATION)
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('partageable', PreferenceCrudController::PREF_FIN_REVENU_PARTAGEABLE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_PARTAGEABLE)
            ->renderAsBadges([
                RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON] => 'dark',
                RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI] => 'success',
            ]);
        $tabAttributs[] = ChoiceField::new('taxable', PreferenceCrudController::PREF_FIN_REVENU_TAXABLE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_TAXABLE);
        $tabAttributs[] = ChoiceField::new('base', PreferenceCrudController::PREF_FIN_REVENU_BASE)
            ->onlyOnDetail()
            ->setChoices(RevenuCrudController::TAB_BASE);
        $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_FIN_REVENU_TAUX)->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('revenuPure', "Revenu pure")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRevenuPure() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('taxeCourtier', ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxeCourtier() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('revenuNet', "Revenu net")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRevenuNet() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('taxeAssureur', ucfirst($this->serviceTaxes->getNomTaxeAssureur()))
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxeAssureur() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('revenuTotale', "Revenu TTC")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRevenuTotale() * 100);
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
            ->setChoices($this->isExoneree() == true ? ChargementCrudController::TAB_TYPE_CHARGEMENT_EXONEREE : ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE)
            //->setChoices(ChargementCrudController::TAB_TYPE)
            ->onlyOnDetail();
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
        $tabAttributs[] = BooleanField::new('validated', "Validée")
            ->renderAsSwitch(false)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('periodeValidite', "Période")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PROD_TRANCHE_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('police', PreferenceCrudController::PREF_PROD_TRANCHE_POLICE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('client', "Client")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('assureur', "Assureur")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('partenaire', "Partenaire")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('produit', "Produit")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateEffet', "Date d'effet")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateExpiration', "Date d'expiration")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateOperation', "Date d'opération")
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateEmition', "Date d'émition")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('autoriteTaxeAssureur', "Aut. Assureurs")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('autoriteTaxeCourtier', "Aut. Courtier")
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PROD_TRANCHE_COTATION)
            ->onlyOnDetail();
        $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_PROD_TRANCHE_TAUX)
            ->setNumDecimals(2)
            ->onlyOnDetail();

        $tabAttributs[] = MoneyField::new('primeTotaleTranche', "Prime")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeTotaleTranche());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        //Les type de commission
        $tabAttributs[] = MoneyField::new('com_reassurance', "Com / Réa")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getComReassurance());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('com_locale', "Com / Loc")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getComLocale());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('com_fronting', "Com / Frtg")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getComFronting());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('com_frais_gestion', "Com / F. Gest")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getComFraisGestion());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('com_autre_chargement', "Com / Autre")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getComAutreChargement());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('revenuTotal', "Revenu total")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRevenuTotal());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('retroCommissionTotale', "Rétro-Com")
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRetroCommissionTotale() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('taxeCourtierTotale', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxeCourtierTotale() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('taxeAssureurTotale', "Taxe " . ucfirst($this->serviceTaxes->getNomTaxeAssureur()))
            ->formatValue(function ($value, Tranche $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxeAssureurTotale() * 100);
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
        //$tabAttributs = [];
        $tabAttributs[] = TextField::new('typeavenant', "Type d'avenant")
            ->onlyOnIndex();
        // $tabAttributs[] = NumberField::new('idAvenant', "Id. Avenant")
        //     ->onlyOnIndex();
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('assureur', "Assureur")
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('client', "Client")
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('produit', "Couverture")
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('primeTotale', "Prime totale")
            ->formatValue(function ($value, Police $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeTotale());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('assistant', PreferenceCrudController::PREF_PRO_POLICE_ASSISTANT)
            ->onlyOnIndex();
        // $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)
        //     ->onlyOnIndex();
        //Je suis ici
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
            ->onlyOnIndex();

        return $tabAttributs;
    }

    public function setFIN_Fields_Facture_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = ChoiceField::new('status', PreferenceCrudController::PREF_FIN_FACTURE_STATUS)
            ->onlyOnIndex()
            ->setChoices(FactureCrudController::TAB_STATUS_FACTURE)
            ->renderAsBadges([
                FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_SOLDEE] => 'success', //info
                FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_IMPAYEE] => 'danger', //info
                FactureCrudController::TAB_STATUS_FACTURE[FactureCrudController::STATUS_FACTURE_ENCOURS] => 'info', //info
            ]);
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
            ->onlyOnIndex()
            ->setChoices(FactureCrudController::TAB_TYPE_NOTE);
        $tabAttributs[] = AssociationField::new('elementFactures', PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)
            ->formatValue(function ($value, Facture $entity) {
                return count($entity->getElementFactures()) == 0 ? "Aucun élément" : count($entity->getElementFactures()) . " élement(s).";
            })
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_FIN_FACTURE_DESCRIPTION)
            ->renderAsHtml(true)
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('totalDu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_DU)
            ->formatValue(function ($value, Facture $facture) {
                $this->setTotauxFacture($facture);
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($facture->getTotalDu());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('totalRecu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_RECU)
            ->formatValue(function ($value, Facture $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalRecu());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('totalSolde', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_SOLDE)
            ->formatValue(function ($value, Facture $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalSolde());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
            ->onlyOnIndex();



        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE])) {
        //     $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR])) {
        //     $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS])) {
        //     $tabAttributs[] = TextField::new('autreTiers', PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY])) {
        //     $tabAttributs[] = TextField::new('signedBy', PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY])) {
        //     $tabAttributs[] = TextField::new('posteSignedBy', PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES])) {
        //     $tabAttributs[] = AssociationField::new('compteBancaires', PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
        //         ->formatValue(function ($value, Facture $entity) {
        //             return count($entity->getCompteBancaires()) == 0 ? "Aucun Compte Bancaire" : count($entity->getCompteBancaires()) . " compte(s) bancaire(s).";
        //         })
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_PIECE])) {
        //     $tabAttributs[] = AssociationField::new('piece', PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION])) {
        //     $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_CREATION)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION])) {
        //     $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_POLICE_DATE_DE_MODIFICATION)
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR])) {
        //     $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_POLICE_UTILISATEUR)
        //         ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
        //         ->onlyOnIndex();
        // }
        // if ($this->canShow($tabPreferences, $tabDefaultAttributs[PreferenceCrudController::PREF_FIN_FACTURE_ENTREPRISE])) {
        //     $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_FACTURE_ENTREPRISE)
        //         ->onlyOnIndex();
        // }
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
            $tabAttributs[] = MoneyField::new('montantFlat', PreferenceCrudController::PREF_FIN_REVENU_MONTANT_FLAT)
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
        //dd($this->isExoneree());
        if ($this->canShow_url(PreferenceCrudController::PREF_PROD_CHARGEMENT_TYPE)) {
            $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_PROD_CHARGEMENT_TYPE)
                ->setChoices(ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE)
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
                ->setNumDecimals(2)
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
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_ID)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('intitule', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_INTITULE)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('numero', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_NUMERO)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('banque', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_BANQUE)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('codeSwift', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_CODESWIFT)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('codeMonnaie', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_MONNAIE)
            ->onlyOnIndex();
        // $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_DATE_CREATION)
        //     ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_DATE_MODIFICATION)
            ->onlyOnIndex();
        // $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_UTILISATEUR)
        //     ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
        //     ->onlyOnIndex();
        // $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_FIN_COMPTE_BANCAIRE_ENTREPRISE)
        //     ->onlyOnIndex();

        return $tabAttributs;
    }

    public function setFIN_Fields_Revenu_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = BooleanField::new('validated', "Validée?")
            ->renderAsSwitch(false)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_REVENU_TYPE)
            ->onlyOnIndex()
            ->setChoices(RevenuCrudController::TAB_TYPE);
        $tabAttributs[] = ChoiceField::new('partageable', PreferenceCrudController::PREF_FIN_REVENU_PARTAGEABLE)
            ->onlyOnIndex()
            ->renderExpanded()
            ->setChoices(RevenuCrudController::TAB_PARTAGEABLE)
            ->renderAsBadges([
                RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_NON] => 'dark',
                RevenuCrudController::TAB_PARTAGEABLE[RevenuCrudController::PARTAGEABLE_OUI] => 'success',
            ]);
        $tabAttributs[] = ChoiceField::new('taxable', PreferenceCrudController::PREF_FIN_REVENU_TAXABLE)
            ->onlyOnIndex()
            ->renderExpanded()
            ->setChoices(RevenuCrudController::TAB_TAXABLE)
            ->renderAsBadges([
                RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'danger',
                RevenuCrudController::TAB_TAXABLE[RevenuCrudController::TAXABLE_OUI] => 'success',
            ]);
        $tabAttributs[] = ChoiceField::new('base', PreferenceCrudController::PREF_FIN_REVENU_BASE)
            ->onlyOnIndex()
            ->setChoices(RevenuCrudController::TAB_BASE);
        $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_FIN_REVENU_TAUX)
            ->setNumDecimals(2)
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('revenuPure', "Revenu pure")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRevenuPure() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('taxeCourtier', ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxeCourtier() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('revenuNet', "Revenu net")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRevenuNet() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('taxeAssureur', ucfirst($this->serviceTaxes->getNomTaxeAssureur()))
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTaxeAssureur() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('revenuTotale', "Revenu TTC")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRevenuTotale() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('retrocommissionTotale', "Rétrocom")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRetrocommissionTotale() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('reserve', "Réserve")
            ->formatValue(function ($value, Revenu $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getReserve() * 100);
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('police', "Police")
            ->formatValue(function ($value, Revenu $entity) {
                return $entity->getPolice()->getReference();
            })
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('client', "Client")
            ->formatValue(function ($value, Revenu $entity) {
                return $entity->getClient()->getNom();
            })
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('produit', "Produit")
            ->formatValue(function ($value, Revenu $entity) {
                return $entity->getProduit()->getNom();
            })
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('partenaire', "Partenaire")
            ->formatValue(function ($value, Revenu $entity) {
                return $entity->getPartenaire()->getNom();
            })
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', "Modification")
            ->onlyOnIndex();
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
                //->setChoices(ChargementCrudController::TAB_TYPE)
                ->setChoices($this->isExoneree() == true ? ChargementCrudController::TAB_TYPE_CHARGEMENT_EXONEREE : ChargementCrudController::TAB_TYPE_CHARGEMENT_ORDINAIRE)
                ->onlyOnIndex();
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
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PROD_TRANCHE_NOM)
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $value . "</span>";
            })
            ->onlyOnIndex();
        $tabAttributs[] = BooleanField::new('validated', "Validée")
            ->renderAsSwitch(false)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('periodeValidite', "Période")
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $value . "</span>";
            })
            ->onlyOnIndex();
        $tabAttributs[] = PercentField::new('taux', PreferenceCrudController::PREF_PROD_TRANCHE_TAUX)
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $value . "</span>";
            })
            ->setNumDecimals(2)
            ->onlyOnIndex();
        $tabAttributs[] = CollectionField::new('premiumInvoiceDetails', "Prime d'assurance") //primeTotaleTranche
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        //Les type de commission
        $tabAttributs[] = CollectionField::new('comReassuranceInvoiceDetails', "Com / Réa") //com_reassurance
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        $tabAttributs[] = CollectionField::new('comLocaleInvoiceDetails', "Com / Loc") //com_locale
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        $tabAttributs[] = CollectionField::new('comFrontingInvoiceDetails', "Com / Frtg")
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        $tabAttributs[] = CollectionField::new('fraisGestionInvoiceDetails', "Com / F. Gest")
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('revenuTotal', "Revenu total")
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $this->serviceMonnaie->getMonantEnMonnaieAffichage($tranche->getRevenuTotal()) . "</span>";
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = CollectionField::new('retrocomInvoiceDetails', "Rétro-Com")
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        $tabAttributs[] = CollectionField::new('taxCourtierInvoiceDetails', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier()))
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        $tabAttributs[] = CollectionField::new('taxAssureurInvoiceDetails', "Taxe " . ucfirst($this->serviceTaxes->getNomTaxeAssureur()))
            ->setTemplatePath('admin/segment/index_tranche_status.html.twig')
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('reserve', "Réserve")
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $this->serviceMonnaie->getMonantEnMonnaieAffichage($tranche->getReserve()) . "</span>";
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('police', "Police")
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $tranche->getPolice()->getReference() . "</span>";
            })
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('client', "Client")
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $tranche->getClient()->getNom() . "</span>";
            })
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('produit', "Produit")
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $tranche->getProduit()->getNom() . "</span>";
            })
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('partenaire', "Partenaire")
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $tranche->getPartenaire()->getNom() . "</span>";
            })
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PROD_TRANCHE_DERNIRE_MODIFICATION)
            ->formatValue(function ($value, Tranche $tranche) {
                return "<span class='badge badge-light text-bold'>" . $value . "</span>";
            })
            ->onlyOnIndex();

        return $tabAttributs;
    }

    public function setFIN_Fields_Element_Facture_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        // dd("Ici");
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
        $tabAttributs[] = AssociationField::new('tranche', "Tranche")
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
        // dd($this->adminUrlGenerator->get("donnees"));
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_MONTANT)
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('primeTotale', "Prime d'assurance")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('commissionTotale', "Commission")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('fraisGestionTotale', "Frais de gestion")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('revenuTotal', "Revenu total")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('retroCommissionTotale', "Rétro-commission")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('taxeCourtierTotale', "Frais " . ucfirst("" . $this->serviceTaxes->getTaxe(true)->getNom()))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('taxeAssureurTotale', ucfirst("" . $this->serviceTaxes->getTaxe(false)->getNom()))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('tranche', "Tranche")
            ->setColumns(12)
            ->setDisabled(true)
            ->setRequired(false)
            ->onlyOnForms();
        // dd("Ici");

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
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
            ->setChoices(FactureCrudController::TAB_TYPE_NOTE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('elementFactures', PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('totalDu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_DU)
            ->formatValue(function ($value, Facture $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalDu());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('totalRecu', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_RECU)
            ->formatValue(function ($value, Facture $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalRecu());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('totalSolde', PreferenceCrudController::PREF_FIN_FACTURE_TOTAL_SOLDE)
            ->formatValue(function ($value, Facture $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getTotalSolde());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('paiements', PreferenceCrudController::PREF_FIN_FACTURE_PAIEMENTS)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION)->onlyOnDetail();

        return $tabAttributs;
    }

    public function setFIN_Fields_Facture_form($tabAttributs)
    {
        //Section - Principale
        $tabAttributs[] = FormField::addPanel("Section principale")
            ->setIcon("fas fa-location-crosshairs")
            ->setColumns(10)
            ->onlyOnForms(); //fa-solid fa-paperclip
        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_FIN_FACTURE_ASSUREUR)
            ->setRequired(false)
            ->setColumns(5)
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_FIN_FACTURE_PARTENAIRE)
            ->setRequired(false)
            ->setColumns(5)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('autreTiers', PreferenceCrudController::PREF_FIN_FACTURE_AUTRE_TIERS)
            ->setRequired(false)
            ->setColumns(5)
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('piece', PreferenceCrudController::PREF_FIN_FACTURE_PIECE)
            ->setRequired(false)
            ->setColumns(10)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_FIN_FACTURE_TYPE)
            ->setChoices(FactureCrudController::TAB_TYPE_NOTE)
            ->onlyOnForms()
            ->setColumns(5);
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_FIN_FACTURE_REFERENCE)
            ->onlyOnForms()
            ->setColumns(5);

        $tabAttributs[] = TextEditorField::new('description', PreferenceCrudController::PREF_FIN_FACTURE_DESCRIPTION)
            ->setColumns(10)
            ->onlyOnForms();

        $tabAttributs[] = AssociationField::new('compteBancaires', PreferenceCrudController::PREF_FIN_FACTURE_COMPTES_BANCIARES)
            ->setRequired(false)
            ->setColumns(10)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('signedBy', PreferenceCrudController::PREF_FIN_FACTURE_SIGNED_BY)
            ->setRequired(true)
            ->setColumns(5)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('posteSignedBy', PreferenceCrudController::PREF_FIN_FACTURE_POSTE_SIGNED_BY)
            ->setRequired(true)
            ->setColumns(5)
            ->onlyOnForms();

        //Onglet Article
        $tabAttributs[] = FormField::addTab(' Articles facturés')
            ->setIcon('fas fa-handshake')
            ->setHelp("Les articles de la facture.")
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('montantTTC', "Total à payer")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->setColumns(10)
            ->onlyOnForms();

        $tabAttributs[] = FormField::addPanel("Articles facturés")
            ->setIcon("fa-solid fa-layer-group")
            ->setHelp("Elements constitutifs de la facture ou de la note de débit/crédit.")
            ->onlyOnForms(); //fa-solid fa-paperclip
        $tabAttributs[] = CollectionField::new('elementFactures', PreferenceCrudController::PREF_FIN_FACTURE_ELEMENTS)
            ->useEntryCrudForm(ElementFactureCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(10)
            ->onlyOnForms();

        //Section - Documents
        $tabAttributs[] = FormField::addTab("Documents ou pièces jointes")
            ->setIcon("fa-solid fa-paperclip")
            ->setHelp("Merci d'attacher vos pièces justificatives par ici.")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
            //->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(DocPieceCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(12)
            ->onlyOnForms();
        // dd("Ici");

        //return $tabAttributs;
        return $this->appliquerCanDesable($this->appliquerCanHide($tabAttributs));
    }

    public function setCRM_Fields_Produits_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_PRODUIT_ID)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
            ->renderAsHtml()
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
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PRODUIT_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PRODUIT_ENTREPRISE)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION)
            ->onlyOnDetail();

        return $tabAttributs;
    }

    public function setCRM_Fields_Produits_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('iard', PreferenceCrudController::PREF_PRO_PRODUIT_IARD)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_IARD)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('obligatoire', PreferenceCrudController::PREF_PRO_PRODUIT_OBJIGATOIRE)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('abonnement', PreferenceCrudController::PREF_PRO_PRODUIT_ABONNEMENT)
            ->setChoices(ProduitCrudController::TAB_PRODUIT_OUI_NON)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_PRO_PRODUIT_DESCRIPTION)
            ->renderAsHtml()
            ->onlyOnIndex();
        $tabAttributs[] = PercentField::new('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
            ->setNumDecimals(2)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION)
            ->onlyOnIndex();
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
        //$tabAttributs = $this->setAttributs_CalculablesTaxes_Details($tabAttributs);
        return $tabAttributs;
    }

    public function setCRM_Fields_Taxes_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_FIN_TAXE_ID)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_TAXE_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = PercentField::new('tauxIARD', PreferenceCrudController::PREF_FIN_TAXE_TAUX_IARD)
            ->setNumDecimals(2)
            ->onlyOnIndex();
        $tabAttributs[] = PercentField::new('tauxVIE', PreferenceCrudController::PREF_FIN_TAXE_TAUX_VIE)
            ->setNumDecimals(2)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('description', PreferenceCrudController::PREF_FIN_TAXE_DESCRIPTION)
            ->renderAsHtml()
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('organisation', PreferenceCrudController::PREF_FIN_TAXE_ORGANISATION)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('payableparcourtier', PreferenceCrudController::PREF_FIN_TAXE_PAR_COURTIER)
            ->setChoices(TaxeCrudController::TAB_TAXE_PAYABLE_PAR_COURTIER)
            ->onlyOnIndex();
        // $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_FIN_TAXE_UTILISATEUR)
        //     ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
        //     ->onlyOnIndex();
        // $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_FIN_TAXE_DATE_DE_CREATION)
        //     ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_TAXE_DERNIERE_MODIFICATION)
            ->onlyOnIndex();

        //LES CHAMPS CALCULABLES
        //$tabAttributs = $this->setAttributs_CalculablesTaxes_Index($tabAttributs, $tabPreferences, $tabDefaultAttributs);
        return $tabAttributs;
    }

    public function setCRM_Fields_Polices_form($tabAttributs)
    {
        // dd($this->entityInstance);
        $tauxAssureur = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), false);
        $tauxCourtier = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);
        //dd($taux);
        //dd($this->canHide($this->adminUrlGenerator, PreferenceCrudController::PREF_PRO_POLICE_COTATION));
        $tabAttributs[] = AssociationField::new('cotation', PreferenceCrudController::PREF_PRO_POLICE_COTATION)
            ->onlyOnForms()
            ->setColumns(12)
            ->setRequired(false)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                if ($this->entityInstance instanceof Piste) {
                    /** @var Piste */
                    $piste = $this->entityInstance;
                    if ($this->isNewPiste == false) {
                        return $entityRepository
                            ->createQueryBuilder('e')
                            ->Where('e.entreprise = :ese')
                            ->andWhere('e.piste = :piste')
                            ->setParameter('piste', $piste)
                            ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                    } else {
                        return $entityRepository
                            ->createQueryBuilder('e')
                            ->Where('e.entreprise = :ese')
                            ->andWhere('e.validated = :val')
                            ->andWhere('e.piste = :piste')
                            ->setParameter('val', 0)
                            ->setParameter('piste', null)
                            ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                    }
                } else {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                }
            });
        $tabAttributs[] = TextField::new('reference', PreferenceCrudController::PREF_PRO_POLICE_REFERENCE)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = DateTimeField::new('dateoperation', PreferenceCrudController::PREF_PRO_POLICE_DATE_OPERATION)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = DateTimeField::new('dateemission', PreferenceCrudController::PREF_PRO_POLICE_DATE_EMISSION)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = DateTimeField::new('dateeffet', PreferenceCrudController::PREF_PRO_POLICE_DATE_EFFET)
            ->onlyOnForms()
            ->setColumns(12);

        if ($this->piste != null) {
            if (count($this->piste->getPolices()) != 0) {
                $tabAttributs[] = DateTimeField::new('dateexpiration', PreferenceCrudController::PREF_PRO_POLICE_DATE_EXPIRATION)
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = TextField::new('idAvenant', PreferenceCrudController::PREF_PRO_POLICE_ID_AVENANT)
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = TextField::new('typeavenant', PreferenceCrudController::PREF_PRO_POLICE_TYPE_AVENANT)
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = TextField::new('produit', "Couverture")
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = TextField::new('client', "Client")
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = TextField::new('gestionnaire', PreferenceCrudController::PREF_PRO_POLICE_GESTIONNAIRE)
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = TextField::new('assistant', PreferenceCrudController::PREF_PRO_POLICE_ASSISTANT)
                    ->onlyOnForms()
                    ->setColumns(12)
                    ->setDisabled(true);

                //Section - Documents
                $tabAttributs[] = FormField::addPanel("Documents ou pièces jointes")
                    ->setIcon("fa-solid fa-paperclip")
                    ->onlyOnForms(); //fa-solid fa-paperclip
                $tabAttributs[] = CollectionField::new('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
                    //->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
                    ->useEntryCrudForm(DocPieceCrudController::class)
                    ->allowAdd(true)
                    ->allowDelete(true)
                    ->setEntryIsComplex()
                    ->setRequired(false)
                    ->setColumns(12)
                    ->onlyOnForms();

                //Section - Contacts
                $tabAttributs[] = FormField::addPanel("Contacts")
                    ->setIcon("fas fa-address-book")
                    ->setHelp("Personnes impliquées dans les échanges.")
                    ->onlyOnForms();
                $tabAttributs[] = CollectionField::new('contacts', "Contacts")
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);

                //Section - Primes d'assurance
                $tabAttributs[] = FormField::addPanel("Prime d'assurance")
                    ->setIcon("fa-solid fa-cash-register")
                    ->setHelp("Prime d'assurance résultant de la mise en place de l'avenant.")
                    ->onlyOnForms();
                $tabAttributs[] = CollectionField::new('chargements', "Structure")
                    ->onlyOnForms()
                    ->setColumns(12)
                    ->setDisabled(true);
                $tabAttributs[] = MoneyField::new('primeTotale', "Prime totale")
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->setColumns(12)
                    ->setDisabled(true)
                    ->onlyOnForms();

                //Section - Termes de paiement
                $tabAttributs[] = FormField::addPanel("Termes de paiement de la prime")
                    ->setIcon("fa-solid fa-cash-register")
                    ->setHelp("La manière dont la prime d'assurance devra être versée par le client.")
                    ->onlyOnForms();
                $tabAttributs[] = CollectionField::new('tranches', "Structure")
                    ->onlyOnForms()
                    ->setColumns(12)
                    ->setDisabled(true);
                $tabAttributs[] = CollectionField::new('tranches', "Structure")
                    ->onlyOnForms()
                    ->setColumns(12)
                    ->setDisabled(true);

                //Section - Commission de courtage
                $tabAttributs[] = FormField::addPanel("Commission de courtage")
                    ->setIcon("fa-solid fa-cash-register")
                    ->setHelp("Les différents revenus du courtier d'assurance.")
                    ->onlyOnForms();
                $tabAttributs[] = CollectionField::new('revenus', "Structure")
                    ->onlyOnForms()
                    ->setColumns(12)
                    ->setDisabled(true);
                $tabAttributs[] = MoneyField::new('revenuNetTotal', "Revenu pure")
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->setColumns(12)
                    ->setDisabled(true)
                    ->onlyOnForms();
                $tabAttributs[] = MoneyField::new('taxeCourtierTotale', ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxCourtier * 100) . "%)"))
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->setColumns(12)
                    ->setDisabled(true)
                    ->onlyOnForms();
                $tabAttributs[] = MoneyField::new('commissionTotaleHT', "Revenu hors taxe")
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->setColumns(12)
                    ->setDisabled(true)
                    ->onlyOnForms();
                $tabAttributs[] = MoneyField::new('taxeAssureur', ucfirst($this->serviceTaxes->getNomTaxeAssureur() . " (" . ($this->isExoneree() == true ? 0 : ($tauxAssureur * 100)) . "%)"))
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->setColumns(12)
                    ->setDisabled(true)
                    ->onlyOnForms();
                $tabAttributs[] = MoneyField::new('commissionTotaleTTC', "Revenu totale")
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->setColumns(12)
                    ->setDisabled(true)
                    ->onlyOnForms();

                //Section - Partenaire
                $tabAttributs[] = FormField::addPanel("Retrocommission")
                    ->setIcon("fas fa-handshake")
                    ->setHelp("Détails sur la commission à rétrocéder au partenaire.")
                    ->onlyOnForms();
                $tabAttributs[] = TextField::new('partenaire', "Partenaire")
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = PercentField::new('tauxretrocompartenaire', PreferenceCrudController::PREF_CRM_COTATION_TAUX_RETROCOM)
                    ->setColumns(12)
                    ->setHelp("Si différent de 0%, alors c'est le taux ci-dessus qui est appliqué pour la retrocommission.")
                    ->setDisabled(true)
                    ->setNumDecimals(2)
                    ->onlyOnForms();
                $tabAttributs[] = MoneyField::new('revenuTotalHTPartageable', "Revenu HT (partageable)")
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->setHelp("Revenu hors taxe faisant l'objet du partage.")
                    ->setColumns(12)
                    ->setDisabled(true)
                    ->onlyOnForms();
                $tabAttributs[] = MoneyField::new('taxeCourtierTotalePartageable', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxCourtier * 100) . "%)"))
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = MoneyField::new('revenuNetTotalPartageable', "Revenu net (partageable)")
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = MoneyField::new('retroComPartenaire', "Retrocommission")
                    ->setCurrency($this->serviceMonnaie->getCodeSaisie())
                    ->setStoredAsCents()
                    ->onlyOnForms()
                    ->setDisabled(true)
                    ->setColumns(12);
                $tabAttributs[] = MoneyField::new('reserve', "Réserve dû au courtier lui-même")
                    ->setCurrency($this->serviceMonnaie->getCodeAffichage())
                    ->setStoredAsCents()
                    ->setDisabled(true)
                    ->onlyOnForms()
                    ->setColumns(12);
            }
        }

        //return $tabAttributs;
        return $this->appliquerCanDesable($this->appliquerCanHide($tabAttributs));
    }

    public function appliquerCanDesable($tabAttributs): ?array
    {
        foreach ($tabAttributs as $champ) {
            if ($this->canDesable($this->adminUrlGenerator, $champ->getAsDto()->getLabel())) {
                $champ->setDisabled(true);
            }
        }
        return $tabAttributs;
    }

    public function appliquerCanHide($tabAttributs): ?array
    {
        $tabIndexASupprimer = [];
        $index = 0;
        //On identifie les champs à ne pas afficher sur le formulaire
        foreach ($tabAttributs as $champ) {
            if ($this->canHide($this->adminUrlGenerator, $champ->getAsDto()->getLabel())) {
                $tabIndexASupprimer[] = $index;
                //dd("I can hide: " . $champ->getAsDto()->getLabel() . "=" . $index);
            }
            $index = $index + 1;
        }
        //dd($tabIndexASupprimer);
        //On retire les champs à ne pas afficher sur le formulaire
        for ($i = 0; $i < count($tabIndexASupprimer); $i++) {
            $indexASupprimer = $tabIndexASupprimer[$i];
            //dd("Indice à supprimer = " . $indexASupprimer);
            unset($tabAttributs[$indexASupprimer]);
        }
        //dd($tabAttributs);
        return $tabAttributs;
    }

    public function setCRM_Fields_Produits_form($tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PRODUIT_NOM)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_PRO_PRODUIT_CODE)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = PercentField::new('tauxarca', PreferenceCrudController::PREF_PRO_PRODUIT_TAUX_COMMISSION)
            ->setNumDecimals(2)
            ->setColumns(12)
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
            //Je suis ici
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
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('type', PreferenceCrudController::PREF_BIB_DOCUMENT_TYPE)
            ->setChoices(DocPieceCrudController::TAB_TYPES)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextField::new('document', "Pièce jointe")
            ->setFormType(VichFileType::class)
            ->setFormTypeOption("download_label", "Ouvrir le fichier")
            ->setFormTypeOption("delete_label", "Supprimer")
            ->setColumns(12)
            ->onlyOnForms();

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
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_FIN_MONNAIE_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('code', PreferenceCrudController::PREF_FIN_MONNAIE_CODE)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('fonction', PreferenceCrudController::PREF_FIN_MONNAIE_FONCTION)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_FONCTIONS)
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('tauxusd', PreferenceCrudController::PREF_FIN_MONNAIE_TAUX_USD)
            ->setCurrency("USD")
            ->setStoredAsCents()
            ->setNumDecimals(4)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('islocale', PreferenceCrudController::PREF_FIN_MONNAIE_IS_LOCALE)
            ->setChoices(MonnaieCrudController::TAB_MONNAIE_MONNAIE_LOCALE)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_FIN_MONNAIE_DERNIRE_MODIFICATION)
            ->onlyOnIndex();
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
        //je suis ici
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
        //je suis ici
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
        //Je suis ici
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_SIN_SINISTRE_DATE_DE_CREATION)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_SIN_SINISTRE_DERNIRE_MODIFICATION)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_SIN_SINISTRE_ENTREPRISE)
            ->onlyOnDetail();

        //LES CHAMPS CALCULABLES
        // $tabAttributs = $this->setAttributs_Calculables_details(false, $tabAttributs);

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
        // $tabAttributs = $this->setAttributs_Calculables_Index(false, $tabAttributs, $tabPreferences, $tabDefaultAttributs);

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
        //Je suis ici
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
        //Je suis ici
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
        $tabAttributs[] = TextField::new('nomType', PreferenceCrudController::PREF_BIB_DOCUMENT_TYPE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('cotation', "Proposition")
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('police', "Police")
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('piste', "Piste")
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
        //Je suis ici
        return $tabAttributs;
    }

    public function setCRM_Fields_BibliothequePieces_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = TextField::new('logoFormatFichier', "Format")
            ->renderAsHtml()
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_BIB_DOCUMENT_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('nomType', "Type")
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('piste', "Piste")
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('cotation', "Cotation")
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('police', "Police")
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_BIB_DOCUMENT_UTILISATEUR)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_BIB_DOCUMENT_DATE_DE_CREATION)
            ->onlyOnIndex();
        //Je suis ici
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
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)->onlyOnDetail();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)->onlyOnDetail();
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_PARTENAIRE_RCCM)->onlyOnDetail();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_PARTENAIRE_IDNAT)->onlyOnDetail();
        $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_PARTENAIRE_NUM_IMPOT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('pistes', "Pistes")
            ->setTemplatePath('admin/segment/view_pistes.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('polices', "Polices")
            ->setTemplatePath('admin/segment/view_polices.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_PARTENAIRE_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_PARTENAIRE_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION)->onlyOnDetail();
        return $tabAttributs;
    }

    public function setCRM_Fields_Partenaires_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_PARTENAIRE_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = PercentField::new('part', PreferenceCrudController::PREF_PRO_PARTENAIRE_PART)
            ->setNumDecimals(2)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_PARTENAIRE_ADRESSE)
            ->onlyOnIndex();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_PARTENAIRE_EMAIL)
            ->onlyOnIndex();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_PARTENAIRE_SITEWEB)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION)
            ->onlyOnIndex();

        return $tabAttributs;
    }

    public function setCRM_Fields_Clients_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_CLIENT_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CLIENT_NOM)->onlyOnDetail();
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
        $tabAttributs[] = ArrayField::new('cotations', "Propositions")
            ->setTemplatePath('admin/segment/view_cotations.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_CLIENT_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_CLIENT_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION)->onlyOnDetail();
        return $tabAttributs;
    }

    public function setCRM_Fields_Clients_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CLIENT_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('exoneree', "Exonéré")
            ->setChoices([
                'Non' => 0,
                'Oui' => 1
            ])
            ->onlyOnIndex();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CLIENT_TELEPHONE)
            ->onlyOnIndex();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CLIENT_EMAIL)
            ->onlyOnIndex();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_CLIENT_SITEWEB)
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('ispersonnemorale', PreferenceCrudController::PREF_PRO_CLIENT_PERSONNE_MORALE)
            ->onlyOnIndex()
            ->setChoices(ClientCrudController::TAB_CLIENT_IS_PERSONNE_MORALE);
        $tabAttributs[] = ChoiceField::new('secteur', PreferenceCrudController::PREF_PRO_CLIENT_SECTEUR)
            ->onlyOnIndex()
            ->setChoices(ClientCrudController::TAB_CLIENT_SECTEUR);
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CLIENT_DATE_DE_MODIFICATION)
            ->onlyOnIndex();
        //je suis ici
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
        //Je suis ici
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
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_CONTACT_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('poste', PreferenceCrudController::PREF_PRO_CONTACT_POSTE)
            ->onlyOnIndex();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_CONTACT_TELEPHONE)
            ->onlyOnIndex();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_CONTACT_EMAIL)
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_PRO_CONTACT_PISTE)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_CONTACT_DATE_DE_MODIFICATION)
            ->onlyOnIndex();

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
        //je suis ici
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
        //Je suis ici
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
        // $tabAttributs[] = ChoiceField::new('isreassureur', PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR)
        //     ->onlyOnForms()
        //     ->setColumns(6)
        //     ->setChoices([
        //         'Réassureur' => 1,
        //         'Assureur' => 0
        //     ]);
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB)
            ->onlyOnForms()
            ->setColumns(6);
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM)
            ->onlyOnForms()
            ->setColumns(6);
        //je suis ici
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
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_ASSUREUR_NOM)->onlyOnIndex();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE)->onlyOnIndex();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE)->onlyOnIndex();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL)->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('cotations', PreferenceCrudController::PREF_PRO_ASSUREUR_COTATIONS)->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ASSUREUR_UTILISATEUR)
            ->onlyOnIndex()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION)->onlyOnIndex();

        return $tabAttributs;
    }

    public function setCRM_Fields_Assureur_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ASSUREUR_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_ASSUREUR_NOM)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('cotations', PreferenceCrudController::PREF_PRO_ASSUREUR_COTATIONS)
            ->setTemplatePath('admin/segment/view_cotations.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE)->onlyOnDetail();
        $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE)->onlyOnDetail();
        $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL)->onlyOnDetail();
        $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_ASSUREUR_SITE_WEB)->onlyOnDetail();
        $tabAttributs[] = TextField::new('rccm', PreferenceCrudController::PREF_PRO_ASSUREUR_RCCM)->onlyOnDetail();
        $tabAttributs[] = TextField::new('licence', PreferenceCrudController::PREF_PRO_ASSUREUR_LICENCE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('idnat', PreferenceCrudController::PREF_PRO_ASSUREUR_IDNAT)->onlyOnDetail();
        $tabAttributs[] = TextField::new('numimpot', PreferenceCrudController::PREF_PRO_ASSUREUR_NUM_IMPOT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_PRO_ASSUREUR_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        //je suis ici
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_PRO_ASSUREUR_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION)->onlyOnDetail();
        //LES CHAMPS CALCULABLES
        //$tabAttributs = $this->setAttributs_Calculables_details(false, $tabAttributs);
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
        $champsACacher = new ArrayCollection([]);
        if ($adminUrlGenerator->get("champsACacher")) {
            $champsACacher = new ArrayCollection($adminUrlGenerator->get("champsACacher"));
        }
        //dd($champsACacher);
        //dd($nomChamp);
        return $champsACacher->contains($nomChamp);
    }

    private function contient(?array $tab, $donnees): bool
    {
        foreach ($tab as $key => $value) {
            if ($donnees == $value) {
                return true;
            }
        }
        return false;
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
        $tauxArca = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), true);
        $tauxTva = $this->serviceTaxes->getTauxTaxeBranche($this->isIard(), false);
        //dd($this->isExoneree());

        //dd($this->canHide($adminUrlGenerator, PreferenceCrudController::PREF_CRM_COTATION_NOM));
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)
            ->onlyOnForms()
            ->setRequired(false)
            ->setColumns(12);
        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)
            ->setRequired(false)
            ->setColumns(12)
            ->onlyOnForms()
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = BooleanField::new('validated', PreferenceCrudController::PREF_CRM_COTATION_RESULTAT)
            ->setColumns(12)
            ->renderAsSwitch(false) //il reste éditable
            ->setRequired(true)
            ->setDisabled(true)
            ->onlyOnForms();
        $tabAttributs[] = NumberField::new('dureeCouverture', PreferenceCrudController::PREF_CRM_COTATION_DUREE)
            ->setColumns(12)
            ->onlyOnForms();

        //Section - Documents
        $tabAttributs[] = FormField::addPanel("Documents ou pièces jointes")
            ->setIcon("fa-solid fa-paperclip")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
            //->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(DocPieceCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(12)
            ->onlyOnForms();

        //Section - Chargements sur prime d'assurance
        $tabAttributs[] = FormField::addPanel("Détails relatifs à la prime d'assurance")
            ->setIcon("fa-solid fa-cash-register")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('chargements', PreferenceCrudController::PREF_CRM_COTATION_CHARGEMENT)
            ->setHelp("Vous avez la possibilité d'ajouter des données à volonté.")
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
            ->setColumns(12);

        //Section - Termes de paiement
        $tabAttributs[] = FormField::addPanel("Détails relatifs aux termes de paiement.")
            ->setIcon("fa-solid fa-cash-register")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('tranches', PreferenceCrudController::PREF_CRM_COTATION_TRANCHES)
            ->setHelp("Vous avez la possibilité d'ajouter des données à volonté.")
            ->useEntryCrudForm(TrancheCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(12)
            ->onlyOnForms();

        //Section - Commissions
        $tabAttributs[] = FormField::addPanel("Détails relatifs à la commission de courtage")
            ->setIcon("fa-solid fa-cash-register")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('revenus', PreferenceCrudController::PREF_CRM_COTATION_REVENUS)
            ->setHelp("Vous avez la possibilité d'ajouter des données à volonté.")
            ->useEntryCrudForm(RevenuCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('revenuPureTotal', "Revenu pure")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setDisabled(true)
            ->setColumns(12);
        $tabAttributs[] = MoneyField::new('taxeCourtierTotale', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxArca * 100) . "%)"))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setDisabled(true)
            ->setColumns(12);
        $tabAttributs[] = MoneyField::new('revenuNetTotal', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur() . " (net)")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setHelp("La partie partageable + la partie non partageable.")
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setDisabled(true)
            ->setColumns(12);
        $tabAttributs[] = MoneyField::new('taxeAssureurTotale', ucfirst($this->serviceTaxes->getNomTaxeAssureur() . " (" . ($tauxTva * 100) . "%)"))
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setDisabled(true)
            ->setColumns(12);
        $tabAttributs[] = MoneyField::new('revenuTotalTTC', "Revenu TTC")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setDisabled(true)
            ->setColumns(12);

        //Section - rétrocommissions
        $tabAttributs[] = FormField::addPanel("Détails relatifs à la rétrocommission dûe au partenaire")
            ->setIcon("fas fa-handshake")
            ->onlyOnForms();
        $tabAttributs[] = MoneyField::new('revenuNetTotalPartageable', "Revenu hors " . $this->serviceTaxes->getNomTaxeAssureur())
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setHelp("Uniquement la partie partageable avec le partenaire.")
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setDisabled(true)
            ->setColumns(12);
        $tabAttributs[] = MoneyField::new('taxeCourtierTotalePartageable', "Frais " . ucfirst($this->serviceTaxes->getNomTaxeCourtier() . " (" . ($tauxArca * 100) . "%)"))
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
        $tabAttributs[] = MoneyField::new('retroComPartenaire', "Rétrocommission")
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setHelp("Le montant total dû au partenaire.")
            ->setStoredAsCents()
            ->onlyOnForms()
            ->setDisabled(true)
            ->setColumns(12);
        $tabAttributs[] = MoneyField::new('reserve', "Réserve dû au courtier lui-même")
            // ->formatValue(function ($value, Cotation $entity) {
            //     return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getReserve());
            // })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->setDisabled(true)
            ->onlyOnForms()
            ->setColumns(12);

        //je suis ici
        //return $tabAttributs;
        return $this->appliquerCanDesable($this->appliquerCanHide($tabAttributs));
    }

    private function isExoneree(): bool
    {
        //dd($this->adminUrlGenerator->get("isExoneree"));
        $rep = false;
        if ($this->adminUrlGenerator->get("isExoneree")) {
            $rep = $this->adminUrlGenerator->get("isExoneree");
        }
        return $rep;
    }

    private function isIard(): bool
    {
        $rep = false;
        if ($this->adminUrlGenerator->get("isIard")) {
            $rep = $this->adminUrlGenerator->get("isIard");
        }
        //dd($this->adminUrlGenerator);
        //dd($rep);
        return $rep;
    }

    public function setCRM_Fields_Cotation_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = ChoiceField::new('validated', "Status")
            ->onlyOnIndex()
            ->setChoices(CotationCrudController::TAB_TYPE_RESULTAT)
            ->renderAsBadges([
                CotationCrudController::TAB_TYPE_RESULTAT[CotationCrudController::TYPE_RESULTAT_VALIDE] => 'success',
                CotationCrudController::TAB_TYPE_RESULTAT[CotationCrudController::TYPE_RESULTAT_NON_VALIDEE] => 'dark',
            ]);
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
            ->formatValue(function ($value, Cotation $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeTotale());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('client', "Client")->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_COTATION_PISTE)->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('partenaire', "Partenaire")->onlyOnIndex();
        $tabAttributs[] = TextField::new('gestionnaire', "Gestionnaire")->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION)->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION)->onlyOnIndex();
        //je suis ici
        return $tabAttributs;
    }

    public function setCRM_Fields_Cotation_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_COTATION_ID)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('validated', "Status")
            ->setChoices(CotationCrudController::TAB_TYPE_RESULTAT)
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('polices', "Polices")
            ->setTemplatePath('admin/segment/view_polices.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)->onlyOnDetail();
        $tabAttributs[] = NumberField::new('dureeCouverture', PreferenceCrudController::PREF_CRM_COTATION_DUREE)
            ->formatValue(function ($value, Cotation $entity) {
                return $value . " mois.";
            })
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('client', "Client")->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_COTATION_PISTE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('partenaire', "Partenaire")->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('revenus', PreferenceCrudController::PREF_CRM_COTATION_REVENUS)
            ->setTemplatePath('admin/segment/view_revenus.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('chargements', PreferenceCrudController::PREF_CRM_COTATION_CHARGEMENT)
            ->setTemplatePath('admin/segment/view_chargements.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TTC)
            ->formatValue(function ($value, Cotation $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getPrimeTotale());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('tranches', PreferenceCrudController::PREF_CRM_COTATION_TRANCHES)
            ->setTemplatePath('admin/segment/view_tranches.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
            ->setTemplatePath('admin/segment/view_documents.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('gestionnaire', "Gestionnaire")->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION)->onlyOnDetail();
        //Je suis ici
        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = TextField::new('Message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
            ->renderAsHtml()
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('actionCRM', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION)
            ->onlyOnIndex();

        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_FEEDBACK_ID)->onlyOnDetail();
        $tabAttributs[] = TextField::new('Message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
            ->renderAsHtml()
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('actionCRM', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)->onlyOnDetail();
        //Je suis ici
        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_form($tabAttributs)
    {
        $tabAttributs[] = BooleanField::new('closed', "La tâche est exécutée avec succès.")
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextEditorField::new('message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
            ->setColumns(12)
            ->onlyOnForms();

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
        $tabAttributs[] = ChoiceField::new('closed', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
            ->onlyOnDetail()
            ->setChoices(ActionCRMCrudController::STATUS_MISSION)
            ->renderAsBadges([
                ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ACHEVEE] => 'success', //info
                ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS] => 'warning',
            ]);
        $tabAttributs[] = TextField::new('police', PreferenceCrudController::PREF_CRM_MISSION_POLICE)->onlyOnDetail();
        $tabAttributs[] = TextField::new('cotation', PreferenceCrudController::PREF_CRM_MISSION_COTATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('feedbacks', PreferenceCrudController::PREF_CRM_MISSION_FEEDBACKS)
            ->setTemplatePath('admin/segment/view_feedbacks.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR)
            ->onlyOnDetail()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT)->onlyOnDetail();
        //Je suis ici
        return $tabAttributs;
    }

    public function setCRM_Fields_Action_Index(array $tabPreferences, array $tabDefaultAttributs, $tabAttributs)
    {
        $tabAttributs[] = ChoiceField::new('closed', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
            ->onlyOnIndex()
            ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
            ->setChoices(ActionCRMCrudController::STATUS_MISSION)
            ->renderAsBadges([
                ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ACHEVEE] => 'success', //info
                ActionCRMCrudController::STATUS_MISSION[ActionCRMCrudController::MISSION_ENCOURS] => 'warning',
            ]);
        $tabAttributs[] = TextField::new('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
            ->renderAsHtml()
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('feedbacks', PreferenceCrudController::PREF_CRM_MISSION_FEEDBACKS)
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT)
            ->onlyOnIndex();
        //je suis ici
        return $tabAttributs;
    }

    public function setCRM_Fields_Action_form($tabAttributs, $adminUrlGenerator)
    {
        $tabAttributs[] = BooleanField::new('closed', "La tâche est cloturée avec succès.")
            ->onlyOnForms()
            ->setFormTypeOption('disabled', 'disabled')
            ->renderAsSwitch(false)
            ->setColumns(12);
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

        $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
            ->onlyOnForms()
            ->setColumns(12);
        $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
            ->onlyOnForms()
            ->setColumns(12);
        //Section - Documents
        $tabAttributs[] = FormField::addPanel("Pièces jointes et objectif")
            ->setIcon("fa-solid fa-paperclip")
            ->onlyOnForms(); //fa-solid fa-paperclip
        $tabAttributs[] = CollectionField::new('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
            //->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(DocPieceCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(12)
            ->onlyOnForms();
        $tabAttributs[] = TextEditorField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
            //->setFormType(CKEditorType::class)
            ->setRequired(false)
            ->onlyOnForms()
            ->setColumns(12);

        //Section - Feedback de l'action
        $tabAttributs[] = FormField::addPanel("Feedbacks / Comptes Rendus")
            ->setIcon("fas fa-comments")
            ->onlyOnForms(); //fa-solid fa-paperclip
        $tabAttributs[] = CollectionField::new('feedbacks', "Feedbacks")
            //->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(FeedbackCRMCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(12)
            ->onlyOnForms();
        //Je suis ici
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
        //je suis ici
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
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
            ->formatValue(function ($value, Piste $piste) {
                $this->setTitreReportingCRM($piste);
                return $value;
            })
            ->onlyOnIndex();
        $tabAttributs[] = ChoiceField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
            ->setChoices(PisteCrudController::TAB_ETAPES)
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
            ->onlyOnIndex();
        $tabAttributs[] = TextField::new('assureur', PreferenceCrudController::PREF_CRM_PISTE_ASSUREUR)
            ->onlyOnIndex();
        // $tabAttributs[] = AssociationField::new('polices', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
        //     ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
            ->formatValue(function ($value, Piste $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = MoneyField::new('realisation', PreferenceCrudController::PREF_CRM_PISTE_PRIME_TOTALE)
            ->formatValue(function ($value, Piste $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRealisation());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnIndex();
        $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
            ->onlyOnIndex();
        $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_CRM_PISTE_GESTIONNAIRE)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnIndex();
        //Je suis ici
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)
            ->onlyOnIndex();
        return $tabAttributs;
    }

    public function setCRM_Fields_Pistes_Details($tabAttributs)
    {
        $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_PISTE_ID)->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
            ->setChoices(PisteCrudController::TAB_ETAPES)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)->onlyOnDetail();
        $tabAttributs[] = TextareaField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
            ->renderAsHtml()
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
            ->setTemplatePath('admin/segment/view_cotations.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
            ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT)
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('police', "Police source")
            ->onlyOnDetail();

        $tabAttributs[] = ArrayField::new('actionsCRMs', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
            ->setTemplatePath('admin/segment/view_taches.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
            ->onlyOnDetail();

        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
            ->formatValue(function ($value, Piste $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getMontant());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();

        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_CRM_PISTE_GESTIONNAIRE)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('assistant', PreferenceCrudController::PREF_CRM_PISTE_ASSISTANT)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR)
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION)->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_PISTE_ENTREPRISE)->onlyOnDetail();


        //Onglet Contacts
        $tabAttributs[] = FormField::addPanel(" Détails relatifs aux Contacts")
            ->setIcon("fas fa-address-book")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('contacts', "Détails")
            ->setTemplatePath('admin/segment/view_contacts.html.twig')
            ->onlyOnDetail();


        //Onglet Polices
        $tabAttributs[] = FormField::addPanel(' Couverture en place')
            ->setIcon('fas fa-file-shield')
            ->setHelp("Polices d'assurance et/ou avenant mis en place.")
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('polices', "Police en place")
            ->onlyOnDetail();
        $tabAttributs[] = TextField::new('assureur', PreferenceCrudController::PREF_CRM_PISTE_ASSUREUR)
            ->onlyOnDetail();
        $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
            ->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateEffet', "Date d'effet")->onlyOnDetail();
        $tabAttributs[] = DateTimeField::new('dateExpiration', "Date d'expiration")->onlyOnDetail();
        $tabAttributs[] = NumberField::new('duree', "Durée")
            ->formatValue(function ($value, Piste $entity) {
                return $value . " mois.";
            })
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('revenus', "Structure du revenu")
            ->setTemplatePath('admin/segment/view_revenus.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = ArrayField::new('chargements', "Structure de la prime")
            ->setTemplatePath('admin/segment/view_chargements.html.twig')
            ->onlyOnDetail();
        $tabAttributs[] = MoneyField::new('realisation', PreferenceCrudController::PREF_CRM_PISTE_PRIME_TOTALE)
            ->formatValue(function ($value, Piste $entity) {
                return $this->serviceMonnaie->getMonantEnMonnaieAffichage($entity->getRealisation());
            })
            ->setCurrency($this->serviceMonnaie->getCodeAffichage())
            ->setStoredAsCents()
            ->onlyOnDetail();
        //Je suis ici
        $tabAttributs[] = ArrayField::new('tranches', "Termes de paiement")
            ->setTemplatePath('admin/segment/view_tranches.html.twig')
            ->onlyOnDetail();

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
        $tabAttributs[] = FormField::addPanel("Section principale")
            ->setIcon("fas fa-location-crosshairs")
            ->setColumns(10)
            ->onlyOnForms(); //fa-solid fa-paperclip
        $tabAttributs[] = ChoiceField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
            ->setChoices(PisteCrudController::TAB_ETAPES)
            ->onlyOnForms() //
            ->setColumns(10)
            ->setDisabled(true);
        $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
            ->setColumns(10)
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('produit', PreferenceCrudController::PREF_CRM_PISTE_PRODUIT)
            ->onlyOnForms()
            ->setColumns(10)
            ->setRequired(true)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = MoneyField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
            ->setCurrency($this->serviceMonnaie->getCodeSaisie())
            ->setStoredAsCents()
            ->setColumns(3)
            ->onlyOnForms();
        $tabAttributs[] = ChoiceField::new('typeavenant', PreferenceCrudController::PREF_CRM_PISTE_TYPE_AVENANT)
            ->setColumns(10)
            ->onlyOnForms()
            ->setChoices(PoliceCrudController::TAB_POLICE_TYPE_AVENANT);

        $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
            //->setColumns(10)
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('police', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
            ->onlyOnForms()
            ->setColumns(10)
            ->setRequired(false)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('gestionnaire', PreferenceCrudController::PREF_CRM_PISTE_GESTIONNAIRE)
            ->onlyOnForms()
            ->setColumns(10)
            ->setRequired(true)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });
        $tabAttributs[] = AssociationField::new('assistant', PreferenceCrudController::PREF_CRM_PISTE_ASSISTANT)
            ->onlyOnForms()
            ->setColumns(10)
            ->setRequired(true)
            ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                return $entityRepository
                    ->createQueryBuilder('e')
                    ->Where('e.entreprise = :ese')
                    ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
            });


        //Section - Documents
        $tabAttributs[] = FormField::addPanel("Objectif à atteindre et pièces jointes éventuelles")
            ->setIcon("fa-solid fa-paperclip")
            ->onlyOnForms(); //fa-solid fa-paperclip
        $tabAttributs[] = CollectionField::new('documents', PreferenceCrudController::PREF_CRM_COTATION_DOCUMENTS)
            //->setHelp("Vous avez la possibilité d'en ajouter des données à volonté.")
            ->useEntryCrudForm(DocPieceCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(10)
            ->onlyOnForms();
        $tabAttributs[] = TextEditorField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
            ->setColumns(10)
            ->onlyOnForms();




        //Onglet Partenaire
        $tabAttributs[] = FormField::addTab(' Partenaire')
            ->setIcon('fas fa-handshake')
            ->setHelp("Intermédiaire (parrain) à travers lequel vous êtes entrés en contact avec cette piste.")
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('partenaire', PreferenceCrudController::PREF_CRM_PISTE_PARTENAIRE)
            ->setHelp("Si le partenaire n'existe pas encore sur cette liste, vous pouvez l'ajouter. Pour cela, il faut allez sur le champ d'ajout du partenaire.")
            ->onlyOnForms()
            ->setColumns(10)
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
            ->setColumns(10)
            ->onlyOnForms();

        //Onglet Client ou Prospect
        $tabAttributs[] = FormField::addTab(' Client')
            ->setIcon('fas fa-person-shelter')
            ->setHelp("Le client ou prospect concerné par cette piste.")
            ->onlyOnForms();
        $tabAttributs[] = AssociationField::new('client', PreferenceCrudController::PREF_CRM_PISTE_CLIENT)
            ->setHelp("Si le client n'existe pas encore sur cette liste, vous pouvez l'ajouter comme prospect. Pour cela, il faut allez sur le champ d'ajout de prospect.")
            ->onlyOnForms()
            ->setColumns(10)
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
            ->setColumns(10)
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
            ->setColumns(10)
            ->onlyOnForms();

        //Onglet Tache
        $tabAttributs[] = FormField::addTab(' Tâches')
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
            ->setColumns(10)
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
            ->setColumns(10)
            ->onlyOnForms();

        //je suis ici
        //Onglet Polices
        $tabAttributs[] = FormField::addTab(' Couverture en place')
            ->setIcon('fas fa-file-shield')
            ->setHelp("Polices d'assurance et/ou avenant mis en place.")
            ->onlyOnForms();
        $tabAttributs[] = CollectionField::new('polices', PreferenceCrudController::PREF_CRM_PISTE_POLICE)
            ->setHelp("Vous ne pouvez ajouter qu'un seul avenant ou police. Si vous chargez plusieurs, seul le premier enregistrement sera pris en compte.")
            ->useEntryCrudForm(PoliceCrudController::class)
            ->allowAdd(true)
            ->allowDelete(true)
            ->setEntryIsComplex()
            ->setRequired(false)
            ->setColumns(10)
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

    public function setEntite(string $pageName, $entityInstance)
    {
        $this->pageName = $pageName;
        $this->entityInstance = $entityInstance;
        $this->isNewPiste = true;
        if ($this->entityInstance instanceof Piste) {
            $this->piste = $this->entityInstance;
            if ($this->piste->getId()) {
                $this->isNewPiste = false;
            } else {
                $this->isNewPiste = true;
            }
        }
        //dd($this->entityInstance);
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
                    // $this->total_unpaidcommission += $police->getUnpaidcommission();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidcommission) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_COM) {
                    //$this->total_paidcommission += $police->calc_revenu_ttc_encaisse;
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total encaissé: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidcommission) . "]");
                }
                //RETRO-COMMISSION
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_RETROCOM) {
                    // $this->total_unpaidretrocommission += $police->getUnpaidretrocommission();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidretrocommission) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_RETROCOM) {
                    //$this->total_paidretrocommission += $police->calc_retrocom_payees;
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidretrocommission) . "]");
                }
                //TAXES
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE) {
                    // $this->total_unpaidtaxe += $police->getUnpaidtaxe();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidtaxe) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_TAXE) {
                    // $this->total_paidtaxe += $police->getPaidtaxe();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidtaxe) . "]");
                }
                //TAXES COURTIERS
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE_COURTIER) {
                    // $this->total_unpaidtaxecourtier += $police->getUnpaidtaxecourtier();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidtaxecourtier) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_TAXE_COURTIER) {
                    // $this->total_paidtaxecourtier += $police->getPaidtaxecourtier();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidtaxecourtier) . "]");
                }
                //TAXES ASSUREUR
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_UNPAID_TAXE_ASSUREUR) {
                    // $this->total_unpaidtaxeassureur += $police->getUnpaidtaxeassureur();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total dûe: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_unpaidtaxeassureur) . "]");
                }
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PAID_TAXE_ASSUREUR) {
                    // $this->total_paidtaxeassureur += $police->getPaidtaxeassureur();
                    $this->crud->setPageTitle(Crud::PAGE_INDEX, $this->adminUrlGenerator->get("titre") . " - [Total payée: " . $this->serviceMonnaie->getMonantEnMonnaieAffichage($this->total_paidtaxeassureur) . "]");
                }
                //PRODUCTION GLOBALE
                if ($this->adminUrlGenerator->get("codeReporting") == ServiceCrossCanal::REPORTING_CODE_PRODUCTION_TOUS) {
                    // $this->total_prime_nette += $police->getPrimenette();
                    // $this->total_prime_fronting += $police->getFronting();
                    // $this->total_prime_accessoire += $police->getFraisadmin();
                    // $this->total_prime_arca += $police->getArca();
                    // $this->total_prime_tva += $police->getTva();
                    // $this->total_prime_ttc += $police->getPrimetotale();
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
