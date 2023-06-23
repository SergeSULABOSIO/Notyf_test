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
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use App\Controller\Admin\ActionCRMCrudController;
use App\Controller\Admin\PreferenceCrudController;
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
            $tabAttributs = $this->setCRM_Fields_Action_Index_Details($preference, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Action_form($tabAttributs);
        }
        if ($objetInstance instanceof FeedbackCRM) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-comments') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Un feedback est une réponse ou compte rendu attaché à une mission. Chaque mission doit avoir un ou plusieurs feedbacks.")
            ];
            $tabAttributs = $this->setCRM_Fields_Feedback_Index_Details($preference, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Feedback_form($tabAttributs);
        }
        if ($objetInstance instanceof Cotation) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-cash-register') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une cotation est tout simplement un dévis/une offre financière relative à un risque précis. Ce n'est pas une police d'assurance.")
            ];
            $tabAttributs = $this->setCRM_Fields_Cotation_Index_Details($preference, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Cotation_form($tabAttributs);
        }
        if ($objetInstance instanceof EtapeCrm) {
            $tabAttributs = [
                FormField::addPanel('Informations générales')
                    ->setIcon('fas fa-list-check') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une étape (ou phase) dans le traitement d'une pistre. Le traitement d'une piste (càd sa conversion en client) est un processus qui peut passer par un certain nombre d'étapes.")
            ];
            $tabAttributs = $this->setCRM_Fields_Etapes_Index_Details($preference, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Etapes_form($tabAttributs);
        }
        if ($objetInstance instanceof Piste) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-location-crosshairs') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Une piste est un prospect (ou client potientiel) à suivre stratégiquement afin de lui convertir en client."),
            ];
            $tabAttributs = $this->setCRM_Fields_Pistes_Index_Details($preference, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Pistes_form($tabAttributs);
        }
        //GROUPE PRODUCTION
        if ($objetInstance instanceof Assureur) {
            $tabAttributs = [
                FormField::addTab(' Informations générales')
                    ->setIcon('fas fa-umbrella') //<i class="fa-sharp fa-solid fa-address-book"></i>
                    ->setHelp("Le preneur des risques en contre partie du versement d'une prime d'assurance et selon les condtions bien spécifiées dans la police.")
            ];
            $tabAttributs = $this->setCRM_Fields_Assureur_Index_Details($preference, $tabAttributs);
            $tabAttributs = $this->setCRM_Fields_Assureur_form($tabAttributs);
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

    public function setCRM_Fields_Assureur_Index_Details(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS[PreferenceCrudController::PREF_PRO_ASSUREUR_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_PRO_ASSUREUR_ID)
            ->hideOnForm();
        }
        if ($this->canShow($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS[PreferenceCrudController::PREF_PRO_ASSUREUR_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_PRO_ASSUREUR_NOM)
            ->hideOnForm();
        }
        if ($this->canShow($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS[PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE])) {
            $tabAttributs[] = TextField::new('adresse', PreferenceCrudController::PREF_PRO_ASSUREUR_ADRESSE)
            ->hideOnForm();
        }
        if ($this->canShow($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS[PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE])) {
            $tabAttributs[] = TelephoneField::new('telephone', PreferenceCrudController::PREF_PRO_ASSUREUR_TELEPHONE)
            ->hideOnForm();
        }
        if ($this->canShow($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS[PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL])) {
            $tabAttributs[] = EmailField::new('email', PreferenceCrudController::PREF_PRO_ASSUREUR_EMAIL)
            ->hideOnForm();
        }
        if ($this->canShow($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS[PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR])) {
            $tabAttributs[] = ChoiceField::new('isreassureur', PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR)
            ->hideOnForm()
            ->setChoices([
                'Réassureur' => 1,
                'Assureur' => 0
            ]);
        }
        if ($this->canShow($preference->getProAssureurs(), PreferenceCrudController::TAB_PRO_ASSUREURS[PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR])) {
            $tabAttributs[] = UrlField::new('siteweb', PreferenceCrudController::PREF_PRO_ASSUREUR_IS_REASSUREUR)
            ->hideOnForm();
        }

        /* 
        
        
        
        
        
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
            ->setColumns(6); */

        /* return [
            AssociationField::new('utilisateur', "Utilisateur")->setColumns(6)->hideOnForm()
            ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]),

            //Ligne 06
            DateTimeField::new('updatedAt', 'Dernière modification')->hideOnform()->setColumns(6),
            //AssociationField::new('entreprise', 'Entreprise')->hideOnindex()->setColumns(6)

            //CHAMPS CALCULABLES ICI EN BAS !!!!!
         */
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

    public function setCRM_Fields_Cotation_Index_Details(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_COTATION_ID)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_COTATION_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_COTATION_PISTE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_RISQUE])) {
            $tabAttributs[] = AssociationField::new('risque', PreferenceCrudController::PREF_CRM_COTATION_RISQUE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE])) {
            $tabAttributs[] = NumberField::new('primeTotale', PreferenceCrudController::PREF_CRM_COTATION_PRIME_TOTALE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_MONNAIE])) {
            $tabAttributs[] = AssociationField::new('monnaie', PreferenceCrudController::PREF_CRM_COTATION_MONNAIE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR])) {
            $tabAttributs[] = ArrayField::new('assureur', PreferenceCrudController::PREF_CRM_COTATION_ASSUREUR)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_PIECES])) {
            $tabAttributs[] = ArrayField::new('pieces', PreferenceCrudController::PREF_CRM_COTATION_PIECES)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_COTATION_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_COTATION_ENTREPRISE)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmCotations(), PreferenceCrudController::TAB_CRM_COTATIONS[PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_COTATION_DATE_MODIFICATION)
                ->hideOnForm();
        }
        return $tabAttributs;
    }

    public function setCRM_Fields_Feedback_Index_Details(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_FEEDBACK_ID)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE])) {
            $tabAttributs[] = TextField::new('Message', PreferenceCrudController::PREF_CRM_FEEDBACK_MESAGE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE])) {
            $tabAttributs[] = TextField::new('prochaineTache', PreferenceCrudController::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION])) {
            $tabAttributs[] = AssociationField::new('action', PreferenceCrudController::PREF_CRM_FEEDBACK_ACTION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET])) {
            $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_EFFET)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_FEEDBACK_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_FEEDBACK_DATE_MODIFICATION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmFeedbacks(), PreferenceCrudController::TAB_CRM_FEEDBACKS[PreferenceCrudController::PREF_CRM_FEEDBACK_ENTREPRISE])) {
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

    public function setCRM_Fields_Action_Index_Details(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_MISSION_ID)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_NOM])) {
            $tabAttributs[] = TextField::new('mission', PreferenceCrudController::PREF_CRM_MISSION_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF])) {
            $tabAttributs[] = TextareaField::new('objectif', PreferenceCrudController::PREF_CRM_MISSION_OBJECTIF)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_STATUS])) {
            $tabAttributs[] = ChoiceField::new('clos', PreferenceCrudController::PREF_CRM_MISSION_STATUS)
                ->hideOnForm()
                ->setHelp("Précisez si cette mission/action est encore en vigueur ou pas.")
                ->setChoices(ActionCRMCrudController::STATUS_MISSION);
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_PISTE])) {
            $tabAttributs[] = AssociationField::new('piste', PreferenceCrudController::PREF_CRM_MISSION_PISTE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT])) {
            $tabAttributs[] = DateTimeField::new('startedAt', PreferenceCrudController::PREF_CRM_MISSION_STARTED_AT)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT])) {
            $tabAttributs[] = DateTimeField::new('endedAt', PreferenceCrudController::PREF_CRM_MISSION_ENDED_AT)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A])) {
            $tabAttributs[] = AssociationField::new('attributedTo', PreferenceCrudController::PREF_CRM_MISSION_ATTRIBUE_A)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_MISSION_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_MISSION_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_MISSION_CREATED_AT)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmMissions(), PreferenceCrudController::TAB_CRM_MISSIONS[PreferenceCrudController::PREF_CRM_MISSION_UPDATED_AT])) {
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


    public function setCRM_Fields_Etapes_Index_Details(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_ETAPES_ID)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_ETAPES_NOM)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_ETAPES_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE])) {
            $tabAttributs[] = AssociationField::new('entreprise', PreferenceCrudController::PREF_CRM_ETAPES_ENTREPRISE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmEtapes(), PreferenceCrudController::TAB_CRM_ETAPES[PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_ETAPES_DATE_MODIFICATION)
                ->hideOnForm();
        }
        return $tabAttributs;
    }




    public function setCRM_Fields_Pistes_Index_Details(Preference $preference, $tabAttributs)
    {
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_ID])) {
            $tabAttributs[] = NumberField::new('id', PreferenceCrudController::PREF_CRM_PISTE_ID)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_NOM])) {
            $tabAttributs[] = TextField::new('nom', PreferenceCrudController::PREF_CRM_PISTE_NOM)
                ->hideOnForm(); //->setColumns(6);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF])) {
            $tabAttributs[] = TextField::new('objectif', PreferenceCrudController::PREF_CRM_PISTE_OBJECTIF)
                ->hideOnForm(); //->setColumns(6);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_MONTANT])) {
            $tabAttributs[] = NumberField::new('montant', PreferenceCrudController::PREF_CRM_PISTE_MONTANT)
                ->hideOnForm(); //->setColumns(6);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_CONTACT])) {
            $tabAttributs[] = CollectionField::new('contact', PreferenceCrudController::PREF_CRM_PISTE_CONTACT)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_COTATION])) {
            $tabAttributs[] = CollectionField::new('cotations', PreferenceCrudController::PREF_CRM_PISTE_COTATION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_ACTIONS])) {
            $tabAttributs[] = CollectionField::new('actions', PreferenceCrudController::PREF_CRM_PISTE_ACTIONS)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_ETAPE])) {
            $tabAttributs[] = AssociationField::new('etape', PreferenceCrudController::PREF_CRM_PISTE_ETAPE)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION])) {
            $tabAttributs[] = DateTimeField::new('expiredAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_EXPIRATION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR])) {
            $tabAttributs[] = AssociationField::new('utilisateur', PreferenceCrudController::PREF_CRM_PISTE_UTILISATEUR)
                ->hideOnForm()
                ->setPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION])) {
            $tabAttributs[] = DateTimeField::new('createdAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_CREATION)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION])) {
            $tabAttributs[] = DateTimeField::new('updatedAt', PreferenceCrudController::PREF_CRM_PISTE_DATE_DE_MODIFICATION)
                ->onlyOnIndex();
        }

        //LES CHAMPS CALCULABLES

        $tabAttributs[] = FormField::addTab(' Attributs calculés')->setIcon('fa-solid fa-temperature-high')
            ->hideOnForm();
        $tabAttributs[] = FormField::addPanel('Primes')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();

        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_tab])) {
            $tabAttributs[] = ArrayField::new('calc_polices_tab', PreferenceCrudController::PREF_calc_polices_tab)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_primes_nette])) {
            $tabAttributs[] = NumberField::new('calc_polices_primes_nette', PreferenceCrudController::PREF_calc_polices_primes_nette)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_fronting])) {
            $tabAttributs[] = NumberField::new('calc_polices_fronting', PreferenceCrudController::PREF_calc_polices_fronting)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_accessoire])) {
            $tabAttributs[] = NumberField::new('calc_polices_accessoire', PreferenceCrudController::PREF_calc_polices_accessoire)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_tva])) {
            $tabAttributs[] = NumberField::new('calc_polices_tva', PreferenceCrudController::PREF_calc_polices_tva)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_polices_primes_totale])) {
            $tabAttributs[] = NumberField::new('calc_polices_primes_totale', PreferenceCrudController::PREF_calc_polices_primes_totale)
                ->hideOnForm();
        }
        //SECTION REVENU

        $tabAttributs[] = FormField::addPanel('Commissions')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();

        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_reserve])) {
            $tabAttributs[] = NumberField::new('calc_revenu_reserve', PreferenceCrudController::PREF_calc_revenu_reserve)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_partageable])) {
            $tabAttributs[] = NumberField::new('calc_revenu_partageable', PreferenceCrudController::PREF_calc_revenu_partageable)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ht])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ht', PreferenceCrudController::PREF_calc_revenu_ht)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ttc', PreferenceCrudController::PREF_calc_revenu_ttc)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc_encaisse])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ttc_encaisse', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc_encaisse_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_revenu_ttc_encaisse_tab_ref_factures', PreferenceCrudController::PREF_calc_revenu_ttc_encaisse_tab_ref_factures)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du])) {
            $tabAttributs[] = NumberField::new('calc_revenu_ttc_solde_restant_du', PreferenceCrudController::PREF_calc_revenu_ttc_solde_restant_du)
                ->hideOnForm();
        }

        //SECTION PARTENAIRES

        $tabAttributs[] = FormField::addPanel('Retrocommossions')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();

        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom])) {
            $tabAttributs[] = NumberField::new('calc_retrocom', PreferenceCrudController::PREF_calc_retrocom)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom_payees])) {
            $tabAttributs[] = NumberField::new('calc_retrocom_payees', PreferenceCrudController::PREF_calc_retrocom_payees)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom_payees_tab_factures])) {
            $tabAttributs[] = ArrayField::new('calc_retrocom_payees_tab_factures', PreferenceCrudController::PREF_calc_retrocom_payees_tab_factures)
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_retrocom_solde])) {
            $tabAttributs[] = NumberField::new('calc_retrocom_solde', PreferenceCrudController::PREF_calc_retrocom_solde)
                ->hideOnForm();
        }

        //SECTION - TAXES

        $tabAttributs[] = FormField::addPanel('Impôts et Taxes')->setIcon('fa-solid fa-toggle-off')
            ->hideOnForm();

        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_courtier_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_courtier_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Desc", PreferenceCrudController::PREF_calc_taxes_courtier_tab)) //
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_courtier])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_courtier))
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_courtier_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Pymnt", PreferenceCrudController::PREF_calc_taxes_courtier_payees))
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_courtier_payees_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_courtier_payees_tab_ref_factures', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "PdP", PreferenceCrudController::PREF_calc_taxes_courtier_payees_tab_ref_factures))
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_courtier_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_courtier_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_COURTIER, "Solde", PreferenceCrudController::PREF_calc_taxes_courtier_solde))
                ->hideOnForm();
        }

        //SECTION - TAXES

        $tabAttributs[] = FormField::addPanel()
            ->hideOnForm();

        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_assureurs_tab])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_tab', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Desc", PreferenceCrudController::PREF_calc_taxes_assureurs_tab))
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_assureurs])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Mnt dû", PreferenceCrudController::PREF_calc_taxes_assureurs))
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_assureurs_payees])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_payees', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Pymnt", PreferenceCrudController::PREF_calc_taxes_assureurs_payees))
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_assureurs_payees_tab_ref_factures])) {
            $tabAttributs[] = ArrayField::new('calc_taxes_assureurs_payees_tab_ref_factures', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "PdP", PreferenceCrudController::PREF_calc_taxes_assureurs_payees_tab_ref_factures))
                ->hideOnForm();
        }
        if ($this->canShow($preference->getCrmPistes(), PreferenceCrudController::TAB_CRM_PISTE[PreferenceCrudController::PREF_calc_taxes_assureurs_solde])) {
            $tabAttributs[] = NumberField::new('calc_taxes_assureurs_solde', $this->getTitreAttributTaxe(self::INDICE_TAXE_ASSUREUR, "Solde", PreferenceCrudController::PREF_calc_taxes_assureurs_solde))
                ->hideOnForm();
        }
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
