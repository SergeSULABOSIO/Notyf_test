<?php

namespace App\Controller\Admin;

use App\Entity\Facture;
use App\Entity\Preference;
use Doctrine\ORM\QueryBuilder;
use App\Service\ServiceEntreprise;
use App\Service\ServicePreferences;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PreferenceCrudController extends AbstractCrudController
{

    //CHAMPS CALCULABLES AUTOMATIQUEMENT
    public const PREF_calc_polices_primes_nette                         = 'Ac/Prime/Mnt ht';
    public const PREF_calc_polices_primes_totale                        = 'Ac/Prime/Mnt Total';
    public const PREF_calc_polices_fronting                             = 'Ac/Prime/Fronting';
    public const PREF_calc_polices_accessoire                           = 'Ac/Prime/Accessoires';
    public const PREF_calc_polices_tva                                  = 'Ac/Prime/Taxes';

    public const PREF_calc_sinistre_dommage_total                       = 'Ac/Sinistre/Dommage';
    public const PREF_calc_sinistre_indemnisation_total                 = 'Ac/Sinistre/Indemn';
    public const PREF_calc_sinistre_indice_SP                           = 'Ac/Sinistre/Indice S/P';

    public const PREF_calc_revenu_reserve                               = 'Ac/Comm./Réserve';
    public const PREF_calc_revenu_partageable                           = 'Ac/Comm./A partager';
    public const PREF_calc_revenu_ht                                    = 'Ac/Comm./Mnt ht';
    public const PREF_calc_revenu_ttc                                   = 'Ac/Comm./Mnt dû';
    public const PREF_calc_revenu_ttc_encaisse                          = 'Ac/Comm./Pymnt';
    public const PREF_calc_revenu_ttc_solde_restant_du                  = 'Ac/Comm./Solde dû';

    public const PREF_calc_retrocom                                     = 'Ac/Retrocom./Mnt dû';
    public const PREF_calc_retrocom_payees                              = 'Ac/Retrocom./Pymnt';
    public const PREF_calc_retrocom_solde                               = 'Ac/Retrocom./Solde';

    public const PREF_calc_taxes_courtier_tab                           = 'Ac/Taxes/Court./Réf.';
    public const PREF_calc_taxes_courtier                               = 'Ac/Taxes/Court./Mnt dû';
    public const PREF_calc_taxes_courtier_payees                        = 'Ac/Taxes/Court./Pymnt';
    public const PREF_calc_taxes_courtier_solde                         = 'Ac/Taxes/Court./Solde';

    public const PREF_calc_taxes_assureurs_tab                          = 'Ac/Taxes/Assur./Réf.';
    public const PREF_calc_taxes_assureurs                              = 'Ac/Taxes/Assur./Mnt dû';
    public const PREF_calc_taxes_assureurs_payees                       = 'Ac/Taxes/Assur./Pymnt';
    public const PREF_calc_taxes_assureurs_solde                        = 'Ac/Taxes/Assur./Solde';


    //CRM - ACTION / MISSION
    public const PREF_CRM_MISSION_ID                = "Id";
    public const PREF_CRM_MISSION_NOM               = "Intitulé";
    public const PREF_CRM_MISSION_OBJECTIF          = "Objectif";
    public const PREF_CRM_MISSION_STATUS            = "Status";
    public const PREF_CRM_MISSION_PISTE             = "Piste";
    public const PREF_CRM_MISSION_POLICE            = "Police";
    public const PREF_CRM_MISSION_SINISTRE          = "Sinistre";
    public const PREF_CRM_MISSION_COTATION          = "Cotation";
    public const PREF_CRM_MISSION_FEEDBACKS         = "Feedbacks";
    public const PREF_CRM_MISSION_STARTED_AT        = "Date d'effet";
    public const PREF_CRM_MISSION_ENDED_AT          = "Echéance";
    public const PREF_CRM_MISSION_ATTRIBUE_A        = "Attribuée à";
    public const PREF_CRM_MISSION_UTILISATEUR       = "Utilisateur";
    public const PREF_CRM_MISSION_ENTREPRISE        = "Entreprise";
    public const PREF_CRM_MISSION_CREATED_AT        = "Date de création";
    public const PREF_CRM_MISSION_UPDATED_AT        = "D. modification";
    public const TAB_CRM_MISSIONS = [
        self::PREF_CRM_MISSION_ID           => 0,
        self::PREF_CRM_MISSION_NOM          => 1,
        self::PREF_CRM_MISSION_OBJECTIF     => 2,
        self::PREF_CRM_MISSION_STATUS       => 3,
        self::PREF_CRM_MISSION_PISTE        => 4,
        self::PREF_CRM_MISSION_POLICE       => 5,
        self::PREF_CRM_MISSION_COTATION     => 6,
        self::PREF_CRM_MISSION_SINISTRE     => 7,
        self::PREF_CRM_MISSION_STARTED_AT   => 8,
        self::PREF_CRM_MISSION_ENDED_AT     => 9,
        self::PREF_CRM_MISSION_ATTRIBUE_A   => 10,
        self::PREF_CRM_MISSION_UTILISATEUR  => 11,
        self::PREF_CRM_MISSION_ENTREPRISE   => 12,
        self::PREF_CRM_MISSION_CREATED_AT   => 13,
        self::PREF_CRM_MISSION_UPDATED_AT   => 14,
        self::PREF_CRM_MISSION_FEEDBACKS    => 15
    ];

    //CRM - FEEDBACK
    public const PREF_CRM_FEEDBACK_ID                   = "Id";
    public const PREF_CRM_FEEDBACK_MESAGE               = "Message";
    public const PREF_CRM_FEEDBACK_PROCHAINE_ETAPE      = "Etape suivante";
    public const PREF_CRM_FEEDBACK_DATE_EFFET           = "Date d'effet";
    public const PREF_CRM_FEEDBACK_ACTION               = "Mission concernée";
    public const PREF_CRM_FEEDBACK_DATE_CREATION        = "Date de création";
    public const PREF_CRM_FEEDBACK_DATE_MODIFICATION    = "Dernière modification";
    public const PREF_CRM_FEEDBACK_UTILISATEUR          = "Utilisateur";
    public const PREF_CRM_FEEDBACK_ENTREPRISE           = "Entreprise";
    public const TAB_CRM_FEEDBACKS = [
        self::PREF_CRM_FEEDBACK_ID                  => 0,
        self::PREF_CRM_FEEDBACK_MESAGE              => 1,
        self::PREF_CRM_FEEDBACK_PROCHAINE_ETAPE     => 2,
        self::PREF_CRM_FEEDBACK_DATE_EFFET          => 3,
        self::PREF_CRM_FEEDBACK_ACTION              => 4,
        self::PREF_CRM_FEEDBACK_UTILISATEUR         => 5,
        self::PREF_CRM_FEEDBACK_ENTREPRISE          => 6,
        self::PREF_CRM_FEEDBACK_DATE_CREATION       => 7,
        self::PREF_CRM_FEEDBACK_DATE_MODIFICATION   => 8
    ];
    //CRM - COTATION
    public const PREF_CRM_COTATION_ID                   = "Id";
    public const PREF_CRM_COTATION_RESULTAT             = "L'offre est validée et l'avenant est en vigueur.";
    public const PREF_CRM_COTATION_NOM                  = "Intitulé de l'offre";
    public const PREF_CRM_COTATION_DUREE                = "Durée (en mois)";
    public const PREF_CRM_COTATION_ASSUREUR             = "Assureur";
    public const PREF_CRM_COTATION_DOCUMENTS            = "Documents";
    public const PREF_CRM_COTATION_PRODUIT              = "Couverture d'assurance";
    public const PREF_CRM_COTATION_REVENUS              = "Revenus";
    public const PREF_CRM_COTATION_TAUX_RETROCOM        = "Taux (retrocom)";
    public const PREF_CRM_COTATION_CHARGEMENT           = "Chargements";
    public const PREF_CRM_COTATION_TRANCHES             = "Tranches";
    public const PREF_CRM_COTATION_PRIME_TTC            = "Prime totale";
    public const PREF_CRM_COTATION_REVENU_TOTAL_HT      = "Revenu total ht";
    public const PREF_CRM_COTATION_PISTE                = "Piste concernée";
    public const PREF_CRM_COTATION_DATE_CREATION        = "D. création";
    public const PREF_CRM_COTATION_DATE_MODIFICATION    = "D. modification";
    public const PREF_CRM_COTATION_UTILISATEUR          = "Utilisateur";
    public const PREF_CRM_COTATION_ENTREPRISE           = "Entreprise";
    public const TAB_CRM_COTATIONS = [
        self::PREF_CRM_COTATION_ID                                              => 0,
        self::PREF_CRM_COTATION_NOM                                             => 1,
        self::PREF_CRM_COTATION_ASSUREUR                                        => 2,
        self::PREF_CRM_COTATION_PRODUIT                                         => 3,
        self::PREF_CRM_COTATION_PISTE                                           => 4,
        self::PREF_CRM_COTATION_UTILISATEUR                                     => 5,
        self::PREF_CRM_COTATION_ENTREPRISE                                      => 6,
        self::PREF_CRM_COTATION_DATE_CREATION                                   => 7,
        self::PREF_CRM_COTATION_DATE_MODIFICATION                               => 8,
        self::PREF_CRM_COTATION_RESULTAT                                        => 9,
        self::PREF_CRM_COTATION_REVENUS                                         => 10,
        self::PREF_CRM_COTATION_CHARGEMENT                                      => 11,
        self::PREF_CRM_COTATION_PRIME_TTC                                       => 12,
        self::PREF_CRM_COTATION_REVENU_TOTAL_HT                                 => 13,
        self::PREF_CRM_COTATION_TRANCHES                                        => 14,
        self::PREF_CRM_COTATION_DUREE                                           => 15,
        self::PREF_CRM_COTATION_TAUX_RETROCOM                                   => 16,
        self::PREF_CRM_COTATION_DOCUMENTS                                       => 17
    ];
    //CRM - ETAPES
    public const PREF_CRM_ETAPES_ID                 = "Id";
    public const PREF_CRM_ETAPES_NOM                = "Nom";
    public const PREF_CRM_ETAPES_PISTES             = "Pistes";
    public const PREF_CRM_ETAPES_UTILISATEUR        = "Utilisateur";
    public const PREF_CRM_ETAPES_ENTREPRISE         = "Entreprise";
    public const PREF_CRM_ETAPES_DATE_CREATION      = "Date de création";
    public const PREF_CRM_ETAPES_DATE_MODIFICATION  = "Dernière modification";
    public const TAB_CRM_ETAPES = [
        self::PREF_CRM_ETAPES_ID                    => 0,
        self::PREF_CRM_ETAPES_NOM                   => 1,
        self::PREF_CRM_ETAPES_UTILISATEUR           => 2,
        self::PREF_CRM_ETAPES_ENTREPRISE            => 3,
        self::PREF_CRM_ETAPES_DATE_CREATION         => 4,
        self::PREF_CRM_ETAPES_DATE_MODIFICATION     => 5,
        self::PREF_CRM_ETAPES_PISTES                => 6
    ];
    //CRM - PISTE
    public const PREF_CRM_PISTE_ID                          = "Id";
    public const PREF_CRM_PISTE_NOM                         = "Intitulé";
    public const PREF_CRM_PISTE_CONTACT                     = "Contacts";
    public const PREF_CRM_PISTE_OBJECTIF                    = "Objectif";
    public const PREF_CRM_PISTE_MONTANT                     = "Potentiel";
    public const PREF_CRM_PISTE_PRIME_TOTALE                = "Prime totale";
    public const PREF_CRM_PISTE_ETAPE                       = "Etape";
    public const PREF_CRM_PISTE_DATE_EXPIRATION             = "Echéance";
    public const PREF_CRM_PISTE_ACTIONS                     = "Tâches";
    public const PREF_CRM_PISTE_COTATION                    = "Proposition";
    public const PREF_CRM_PISTE_UTILISATEUR                 = "Utilisateur";
    public const PREF_CRM_PISTE_ENTREPRISE                  = "Entreprise";
    public const PREF_CRM_PISTE_DATE_DE_CREATION            = "Date de création";
    public const PREF_CRM_PISTE_DATE_DE_MODIFICATION        = "D. Modification";
    public const PREF_CRM_PISTE_TYPE_AVENANT                = "Avenant";
    public const PREF_CRM_PISTE_POLICE                      = "Police";
    public const PREF_CRM_PISTE_CLIENT                      = "Client";
    public const PREF_CRM_PISTE_PARTENAIRE                  = "Partenaire";
    public const PREF_CRM_PISTE_NEW_PARTENAIRE              = "Nouveau partenaire";
    public const PREF_CRM_PISTE_PRODUIT                     = "Produit";
    public const PREF_CRM_PISTE_PROSPECTS                   = "Prospects";
    public const PREF_CRM_PISTE_GESTIONNAIRE                = "Gestionnaire";
    public const PREF_CRM_PISTE_ASSISTANT                   = "Assistant(e)";
    public const PREF_CRM_PISTE_ASSUREUR                    = "Assureur";
    public const TAB_CRM_PISTE = [
        self::PREF_CRM_PISTE_ID                                 => 0,
        self::PREF_CRM_PISTE_NOM                                => 1,
        self::PREF_CRM_PISTE_CONTACT                            => 2,
        self::PREF_CRM_PISTE_OBJECTIF                           => 3,
        self::PREF_CRM_PISTE_MONTANT                            => 4,
        self::PREF_CRM_PISTE_ETAPE                              => 5,
        self::PREF_CRM_PISTE_DATE_EXPIRATION                    => 6,
        self::PREF_CRM_PISTE_ACTIONS                            => 7,
        self::PREF_CRM_PISTE_COTATION                           => 8,
        self::PREF_CRM_PISTE_UTILISATEUR                        => 9,
        self::PREF_CRM_PISTE_ENTREPRISE                         => 10,
        self::PREF_CRM_PISTE_DATE_DE_CREATION                   => 11,
        self::PREF_CRM_PISTE_DATE_DE_MODIFICATION               => 12,
        self::PREF_CRM_PISTE_TYPE_AVENANT                       => 13,
        self::PREF_CRM_PISTE_POLICE                             => 14,
        self::PREF_CRM_PISTE_CLIENT                             => 15,
        self::PREF_CRM_PISTE_PROSPECTS                          => 16,
        self::PREF_CRM_PISTE_PRODUIT                            => 17,
        self::PREF_CRM_PISTE_PARTENAIRE                         => 18,
        self::PREF_CRM_PISTE_NEW_PARTENAIRE                     => 19,
        self::PREF_CRM_PISTE_GESTIONNAIRE                       => 20,
        self::PREF_CRM_PISTE_ASSISTANT                          => 21,
        self::PREF_CRM_PISTE_PRIME_TOTALE                       => 22,
        self::PREF_CRM_PISTE_ASSUREUR                           => 23
    ];
    //PRODUCTION - ASSUEUR
    public const PREF_PRO_ASSUREUR_ID                       = "Id";
    public const PREF_PRO_ASSUREUR_NOM                      = "Nom de l'assureur";
    public const PREF_PRO_ASSUREUR_POLICES                  = "Polices";
    public const PREF_PRO_ASSUREUR_COTATIONS                = "Cotations";
    public const PREF_PRO_ASSUREUR_ADRESSE                  = "Adresse";
    public const PREF_PRO_ASSUREUR_TELEPHONE                = "Téléphone";
    public const PREF_PRO_ASSUREUR_EMAIL                    = "Email";
    public const PREF_PRO_ASSUREUR_SITE_WEB                 = "Site internet";
    public const PREF_PRO_ASSUREUR_RCCM                     = "Rccm";
    public const PREF_PRO_ASSUREUR_IDNAT                    = "Id. nationale";
    public const PREF_PRO_ASSUREUR_LICENCE                  = "Licence/N° Agrmnt";
    public const PREF_PRO_ASSUREUR_NUM_IMPOT                = "N° d'Impôt";
    public const PREF_PRO_ASSUREUR_IS_REASSUREUR            = "Réassureur";
    public const PREF_PRO_ASSUREUR_UTILISATEUR              = "Utilisateur";
    public const PREF_PRO_ASSUREUR_ENTREPRISE               = "Entreprise";
    public const PREF_PRO_ASSUREUR_DATE_DE_CREATION         = "Date de création";
    public const PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION     = "D. modification";
    public const TAB_PRO_ASSUREURS = [
        self::PREF_PRO_ASSUREUR_ID                              => 0,
        self::PREF_PRO_ASSUREUR_NOM                             => 1,
        self::PREF_PRO_ASSUREUR_POLICES                         => 2,
        self::PREF_PRO_ASSUREUR_COTATIONS                       => 3,
        self::PREF_PRO_ASSUREUR_ADRESSE                         => 4,
        self::PREF_PRO_ASSUREUR_TELEPHONE                       => 5,
        self::PREF_PRO_ASSUREUR_EMAIL                           => 6,
        self::PREF_PRO_ASSUREUR_SITE_WEB                        => 7,
        self::PREF_PRO_ASSUREUR_RCCM                            => 8,
        self::PREF_PRO_ASSUREUR_IDNAT                           => 9,
        self::PREF_PRO_ASSUREUR_LICENCE                         => 10,
        self::PREF_PRO_ASSUREUR_NUM_IMPOT                       => 11,
        self::PREF_PRO_ASSUREUR_IS_REASSUREUR                   => 12,
        self::PREF_PRO_ASSUREUR_UTILISATEUR                     => 13,
        self::PREF_PRO_ASSUREUR_ENTREPRISE                      => 14,
        self::PREF_PRO_ASSUREUR_DATE_DE_CREATION                => 15,
        self::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION            => 16,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_primes_nette                    => 17,
        self::PREF_calc_polices_primes_totale                   => 18,
        self::PREF_calc_polices_fronting                        => 19,
        self::PREF_calc_polices_accessoire                      => 20,
        self::PREF_calc_polices_tva                             => 21,
        self::PREF_calc_revenu_reserve                          => 22,
        self::PREF_calc_revenu_partageable                      => 23,
        self::PREF_calc_revenu_ht                               => 24,
        self::PREF_calc_revenu_ttc                              => 25,
        self::PREF_calc_revenu_ttc_encaisse                     => 26,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 27,
        self::PREF_calc_retrocom                                => 28,
        self::PREF_calc_retrocom_payees                         => 29,
        self::PREF_calc_retrocom_solde                          => 30,
        self::PREF_calc_taxes_courtier_tab                      => 31,
        self::PREF_calc_taxes_courtier                          => 32,
        self::PREF_calc_taxes_courtier_payees                   => 33,
        self::PREF_calc_taxes_courtier_solde                    => 34,
        self::PREF_calc_taxes_assureurs_tab                     => 35,
        self::PREF_calc_taxes_assureurs                         => 36,
        self::PREF_calc_taxes_assureurs_payees                  => 37,
        self::PREF_calc_taxes_assureurs_solde                   => 38,
        self::PREF_calc_sinistre_dommage_total                  => 39,
        self::PREF_calc_sinistre_indemnisation_total            => 40,
        self::PREF_calc_sinistre_indice_SP                      => 41
    ];
    //PRODUCTION - ENGIN
    public const PREF_PRO_ENGIN_ID                          = "Id";
    public const PREF_PRO_ENGIN_MODEL                       = "Modèl";
    public const PREF_PRO_ENGIN_MARQUE                      = "Marque";
    public const PREF_PRO_ENGIN_ANNEE                       = "Année";
    public const PREF_PRO_ENGIN_PUISSANCE                   = "Puissance";
    public const PREF_PRO_ENGIN_VALEUR                      = "Valeur";
    public const PREF_PRO_ENGIN_NB_SIEGES                   = "Nb. Sièges";
    public const PREF_PRO_ENGIN_USAGE                       = "Usage";
    public const PREF_PRO_ENGIN_NATURE                      = "Nature";
    public const PREF_PRO_ENGIN_N°_PLAQUE                   = "Plaque d'imm.";
    public const PREF_PRO_ENGIN_N°_CHASSIS                  = "N° Chassis";
    public const PREF_PRO_ENGIN_POLICE                      = "Police";
    public const PREF_PRO_ENGIN_UTILISATEUR                 = "Utilisateur";
    public const PREF_PRO_ENGIN_ENTREPRISE                  = "Entreprise";
    public const PREF_PRO_ENGIN_DATE_DE_CREATION            = "Date de création";
    public const PREF_PRO_ENGIN_DATE_DE_MODIFICATION        = "Dernière modification";
    public const TAB_PRO_ENGINS = [
        self::PREF_PRO_ENGIN_ID                     => 0,
        self::PREF_PRO_ENGIN_MODEL                  => 1,
        self::PREF_PRO_ENGIN_MARQUE                 => 2,
        self::PREF_PRO_ENGIN_ANNEE                  => 3,
        self::PREF_PRO_ENGIN_PUISSANCE              => 4,
        self::PREF_PRO_ENGIN_VALEUR                 => 5,
        self::PREF_PRO_ENGIN_NB_SIEGES              => 6,
        self::PREF_PRO_ENGIN_USAGE                  => 7,
        self::PREF_PRO_ENGIN_NATURE                 => 8,
        self::PREF_PRO_ENGIN_N°_PLAQUE              => 9,
        self::PREF_PRO_ENGIN_N°_CHASSIS             => 10,
        self::PREF_PRO_ENGIN_POLICE                 => 11,
        self::PREF_PRO_ENGIN_UTILISATEUR            => 12,
        self::PREF_PRO_ENGIN_ENTREPRISE             => 13,
        self::PREF_PRO_ENGIN_DATE_DE_CREATION       => 14,
        self::PREF_PRO_ENGIN_DATE_DE_MODIFICATION   => 15
    ];
    //PRODUCTION - CONTACT
    public const PREF_PRO_CONTACT_ID                        = "Id";
    public const PREF_PRO_CONTACT_NOM                       = "Nom";
    public const PREF_PRO_CONTACT_POSTE                     = "Poste";
    public const PREF_PRO_CONTACT_TELEPHONE                 = "Téléphone";
    public const PREF_PRO_CONTACT_EMAIL                     = "Email";
    public const PREF_PRO_CONTACT_PISTE                     = "Piste";
    public const PREF_PRO_CONTACT_UTILISATEUR               = "Utilisateur";
    public const PREF_PRO_CONTACT_ENTREPRISE                = "Entreprise";
    public const PREF_PRO_CONTACT_DATE_DE_CREATION          = "Date de création";
    public const PREF_PRO_CONTACT_DATE_DE_MODIFICATION      = "D. modification";
    public const TAB_PRO_CONTACTS = [
        self::PREF_PRO_CONTACT_ID                       => 0,
        self::PREF_PRO_CONTACT_NOM                      => 1,
        self::PREF_PRO_CONTACT_POSTE                    => 2,
        self::PREF_PRO_CONTACT_TELEPHONE                => 3,
        self::PREF_PRO_CONTACT_EMAIL                    => 4,
        self::PREF_PRO_CONTACT_PISTE                    => 5,
        self::PREF_PRO_CONTACT_UTILISATEUR              => 6,
        self::PREF_PRO_CONTACT_ENTREPRISE               => 7,
        self::PREF_PRO_CONTACT_DATE_DE_CREATION         => 8,
        self::PREF_PRO_CONTACT_DATE_DE_MODIFICATION     => 9
    ];
    //PRODUCTION - CLIENT
    public const PREF_PRO_CLIENT_ID                         = "Id";
    public const PREF_PRO_CLIENT_NOM                        = "Raison sociale";
    public const PREF_PRO_CLIENT_PERSONNE_MORALE            = "Forme Jurique";
    public const PREF_PRO_CLIENT_EXONEREE                   = "Le client est exonéré de la taxe";
    public const PREF_PRO_CLIENT_ADRESSE                    = "Adresse";
    public const PREF_PRO_CLIENT_COTATIONS                  = "Cotations";
    public const PREF_PRO_CLIENT_POLICES                    = "Polices";
    public const PREF_PRO_CLIENT_TELEPHONE                  = "Téléphone";
    public const PREF_PRO_CLIENT_EMAIL                      = "Email";
    public const PREF_PRO_CLIENT_SITEWEB                    = "Site Internet";
    public const PREF_PRO_CLIENT_RCCM                       = "Rccm";
    public const PREF_PRO_CLIENT_IDNAT                      = "Id. Nationale";
    public const PREF_PRO_CLIENT_NUM_IMPOT                  = "N°. Impôt";
    public const PREF_PRO_CLIENT_SECTEUR                    = "Secteur d'activité";
    public const PREF_PRO_CLIENT_UTILISATEUR                = "Utilisateur";
    public const PREF_PRO_CLIENT_ENTREPRISE                 = "Entreprise";
    public const PREF_PRO_CLIENT_DATE_DE_CREATION           = "Date de création";
    public const PREF_PRO_CLIENT_DATE_DE_MODIFICATION       = "D. modification";
    public const TAB_PRO_CLIENTS = [
        self::PREF_PRO_CLIENT_ID                                => 0,
        self::PREF_PRO_CLIENT_NOM                               => 1,
        self::PREF_PRO_CLIENT_PERSONNE_MORALE                   => 2,
        self::PREF_PRO_CLIENT_ADRESSE                           => 3,
        self::PREF_PRO_CLIENT_TELEPHONE                         => 4,
        self::PREF_PRO_CLIENT_EMAIL                             => 5,
        self::PREF_PRO_CLIENT_SITEWEB                           => 6,
        self::PREF_PRO_CLIENT_RCCM                              => 7,
        self::PREF_PRO_CLIENT_IDNAT                             => 8,
        self::PREF_PRO_CLIENT_NUM_IMPOT                         => 9,
        self::PREF_PRO_CLIENT_COTATIONS                         => 10,
        self::PREF_PRO_CLIENT_SECTEUR                           => 11,
        self::PREF_PRO_CLIENT_UTILISATEUR                       => 12,
        self::PREF_PRO_CLIENT_ENTREPRISE                        => 13,
        self::PREF_PRO_CLIENT_DATE_DE_CREATION                  => 14,
        self::PREF_PRO_CLIENT_DATE_DE_MODIFICATION              => 15,
        self::PREF_PRO_CLIENT_POLICES                           => 16,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_primes_nette                    => 17,
        self::PREF_calc_polices_primes_totale                   => 18,
        self::PREF_calc_polices_fronting                        => 19,
        self::PREF_calc_polices_accessoire                      => 20,
        self::PREF_calc_polices_tva                             => 21,
        self::PREF_calc_revenu_reserve                          => 22,
        self::PREF_calc_revenu_partageable                      => 23,
        self::PREF_calc_revenu_ht                               => 24,
        self::PREF_calc_revenu_ttc                              => 25,
        self::PREF_calc_revenu_ttc_encaisse                     => 26,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 27,
        self::PREF_calc_retrocom                                => 28,
        self::PREF_calc_retrocom_payees                         => 29,
        self::PREF_calc_retrocom_solde                          => 30,
        self::PREF_calc_taxes_courtier_tab                      => 31,
        self::PREF_calc_taxes_courtier                          => 32,
        self::PREF_calc_taxes_courtier_payees                   => 33,
        self::PREF_calc_taxes_courtier_solde                    => 34,
        self::PREF_calc_taxes_assureurs_tab                     => 35,
        self::PREF_calc_taxes_assureurs                         => 36,
        self::PREF_calc_taxes_assureurs_payees                  => 37,
        self::PREF_calc_taxes_assureurs_solde                   => 38,
        self::PREF_calc_sinistre_dommage_total                  => 39,
        self::PREF_calc_sinistre_indemnisation_total            => 40,
        self::PREF_calc_sinistre_indice_SP                      => 41,
        self::PREF_PRO_CLIENT_EXONEREE                          => 42
    ];
    //PRODUCTION - PARTENAIRE
    public const PREF_PRO_PARTENAIRE_ID                         = "Id";
    public const PREF_PRO_PARTENAIRE_NOM                        = "Nom";
    public const PREF_PRO_PARTENAIRE_PART                       = "Part";
    public const PREF_PRO_PARTENAIRE_ADRESSE                    = "Adresse";
    public const PREF_PRO_PARTENAIRE_POLICES                    = "Polices";
    public const PREF_PRO_PARTENAIRE_POP_PARTENAIRE             = "Pdp Partenaire";
    public const PREF_PRO_PARTENAIRE_EMAIL                      = "Email";
    public const PREF_PRO_PARTENAIRE_SITEWEB                    = "Site Internet";
    public const PREF_PRO_PARTENAIRE_RCCM                       = "Rccm";
    public const PREF_PRO_PARTENAIRE_IDNAT                      = "Id. Nationale";
    public const PREF_PRO_PARTENAIRE_NUM_IMPOT                  = "N° Impôt";
    public const PREF_PRO_PARTENAIRE_UTILISATEUR                = "Utilisateur";
    public const PREF_PRO_PARTENAIRE_ENTREPRISE                 = "Entreprise";
    public const PREF_PRO_PARTENAIRE_DATE_DE_CREATION           = "Date de création";
    public const PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION       = "D. modification";
    public const TAB_PRO_PARTENAIRES = [
        self::PREF_PRO_PARTENAIRE_ID                            => 0,
        self::PREF_PRO_PARTENAIRE_NOM                           => 1,
        self::PREF_PRO_PARTENAIRE_PART                          => 2,
        self::PREF_PRO_PARTENAIRE_POLICES                       => 3,
        self::PREF_PRO_PARTENAIRE_POP_PARTENAIRE                => 4,
        self::PREF_PRO_PARTENAIRE_ADRESSE                       => 5,
        self::PREF_PRO_PARTENAIRE_EMAIL                         => 6,
        self::PREF_PRO_PARTENAIRE_SITEWEB                       => 7,
        self::PREF_PRO_PARTENAIRE_RCCM                          => 8,
        self::PREF_PRO_PARTENAIRE_IDNAT                         => 9,
        self::PREF_PRO_PARTENAIRE_NUM_IMPOT                     => 10,
        self::PREF_PRO_PARTENAIRE_UTILISATEUR                   => 11,
        self::PREF_PRO_PARTENAIRE_ENTREPRISE                    => 12,
        self::PREF_PRO_PARTENAIRE_DATE_DE_CREATION              => 13,
        self::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION          => 14,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_primes_nette                    => 15,
        self::PREF_calc_polices_primes_totale                   => 16,
        self::PREF_calc_polices_fronting                        => 17,
        self::PREF_calc_polices_accessoire                      => 18,
        self::PREF_calc_polices_tva                             => 19,
        self::PREF_calc_revenu_reserve                          => 20,
        self::PREF_calc_revenu_partageable                      => 21,
        self::PREF_calc_revenu_ht                               => 22,
        self::PREF_calc_revenu_ttc                              => 23,
        self::PREF_calc_revenu_ttc_encaisse                     => 24,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 25,
        self::PREF_calc_retrocom                                => 26,
        self::PREF_calc_retrocom_payees                         => 27,
        self::PREF_calc_retrocom_solde                          => 28,
        self::PREF_calc_taxes_courtier_tab                      => 29,
        self::PREF_calc_taxes_courtier                          => 30,
        self::PREF_calc_taxes_courtier_payees                   => 31,
        self::PREF_calc_taxes_courtier_solde                    => 32,
        self::PREF_calc_taxes_assureurs_tab                     => 33,
        self::PREF_calc_taxes_assureurs                         => 34,
        self::PREF_calc_taxes_assureurs_payees                  => 35,
        self::PREF_calc_taxes_assureurs_solde                   => 36,
        self::PREF_calc_sinistre_dommage_total                  => 37,
        self::PREF_calc_sinistre_indemnisation_total            => 38,
        self::PREF_calc_sinistre_indice_SP                      => 39
    ];
    //PRODUCTION - POLICE
    public const PREF_PRO_POLICE_ID                                         = "id";
    public const PREF_PRO_POLICE_REFERENCE                                  = "Référence";
    public const PREF_PRO_POLICE_DATE_OPERATION                             = "Date d'opération";
    public const PREF_PRO_POLICE_DATE_EMISSION                              = "Date d'émission";
    public const PREF_PRO_POLICE_DATE_EFFET                                 = "Date d'effet";
    public const PREF_PRO_POLICE_DATE_EXPIRATION                            = "Echéance";
    public const PREF_PRO_POLICE_ID_AVENANT                                 = "Id de l'avenant";
    public const PREF_PRO_POLICE_TYPE_AVENANT                               = "Type d'avenant";
    public const PREF_PRO_POLICE_COTATION                                   = "Proposition";
    public const PREF_PRO_POLICE_GESTIONNAIRE                               = "Gestionnaire";
    public const PREF_PRO_POLICE_ASSISTANT                                  = "Assistant";
    public const PREF_PRO_POLICE_UTILISATEUR                                = "Utilisateur";
    public const PREF_PRO_POLICE_ENTREPRISE                                 = "Entreprise";
    public const PREF_PRO_POLICE_DATE_DE_CREATION                           = "Date de création";
    public const PREF_PRO_POLICE_DATE_DE_MODIFICATION                       = "D. modification";

    public const TAB_PRO_POLICES = [
        self::PREF_PRO_POLICE_ID                                => 0,
        self::PREF_PRO_POLICE_REFERENCE                         => 1,
        self::PREF_PRO_POLICE_DATE_OPERATION                    => 2,
        self::PREF_PRO_POLICE_DATE_EMISSION                     => 3,
        self::PREF_PRO_POLICE_DATE_EFFET                        => 4,
        self::PREF_PRO_POLICE_DATE_EXPIRATION                   => 5,
        self::PREF_PRO_POLICE_ID_AVENANT                        => 6,
        self::PREF_PRO_POLICE_TYPE_AVENANT                      => 7,
        self::PREF_PRO_POLICE_GESTIONNAIRE                      => 8,
        self::PREF_PRO_POLICE_UTILISATEUR                       => 9,
        self::PREF_PRO_POLICE_ENTREPRISE                        => 10,
        self::PREF_PRO_POLICE_DATE_DE_CREATION                  => 11,
        self::PREF_PRO_POLICE_DATE_DE_MODIFICATION              => 12,
        self::PREF_PRO_POLICE_COTATION                          => 13,
        self::PREF_PRO_POLICE_ASSISTANT                         => 14,
    ];
    //PRODUCTION - PRODUIT
    public const PREF_PRO_PRODUIT_ID                        = "Id";
    public const PREF_PRO_PRODUIT_CODE                      = "Code";
    public const PREF_PRO_PRODUIT_NOM                       = "Nom";
    public const PREF_PRO_PRODUIT_DESCRIPTION               = "Description";
    public const PREF_PRO_PRODUIT_TAUX_COMMISSION           = "Taux/Comm.";
    public const PREF_PRO_PRODUIT_OBJIGATOIRE               = "Obligatoire";
    public const PREF_PRO_PRODUIT_ABONNEMENT                = "Abonnement";
    public const PREF_PRO_PRODUIT_IARD                      = "Branche";
    public const PREF_PRO_PRODUIT_COTATIONS                 = "Cotations";
    public const PREF_PRO_PRODUIT_POLICES                   = "Polices";
    public const PREF_PRO_PRODUIT_UTILISATEUR               = "Utilisateur";
    public const PREF_PRO_PRODUIT_ENTREPRISE                = "Entreprise";
    public const PREF_PRO_PRODUIT_DATE_DE_CREATION          = "Date de création";
    public const PREF_PRO_PRODUIT_DATE_DE_MODIFICATION      = "D. modification";
    public const TAB_PRO_PRODUITS = [
        self::PREF_PRO_PRODUIT_ID                               => 0,
        self::PREF_PRO_PRODUIT_CODE                             => 1,
        self::PREF_PRO_PRODUIT_NOM                              => 2,
        self::PREF_PRO_PRODUIT_DESCRIPTION                      => 3,
        self::PREF_PRO_PRODUIT_TAUX_COMMISSION                  => 4,
        self::PREF_PRO_PRODUIT_OBJIGATOIRE                      => 5,
        self::PREF_PRO_PRODUIT_ABONNEMENT                       => 6,
        self::PREF_PRO_PRODUIT_UTILISATEUR                      => 8,
        self::PREF_PRO_PRODUIT_ENTREPRISE                       => 9,
        self::PREF_PRO_PRODUIT_DATE_DE_CREATION                 => 10,
        self::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION             => 11,
        self::PREF_PRO_PRODUIT_COTATIONS                        => 12,
        self::PREF_PRO_PRODUIT_POLICES                          => 13,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_primes_nette                    => 14,
        self::PREF_calc_polices_primes_totale                   => 15,
        self::PREF_calc_polices_fronting                        => 16,
        self::PREF_calc_polices_accessoire                      => 17,
        self::PREF_calc_polices_tva                             => 18,
        self::PREF_calc_revenu_reserve                          => 19,
        self::PREF_calc_revenu_partageable                      => 20,
        self::PREF_calc_revenu_ht                               => 21,
        self::PREF_calc_revenu_ttc                              => 22,
        self::PREF_calc_revenu_ttc_encaisse                     => 23,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 24,
        self::PREF_calc_retrocom                                => 25,
        self::PREF_calc_retrocom_payees                         => 26,
        self::PREF_calc_retrocom_solde                          => 27,
        self::PREF_calc_taxes_courtier_tab                      => 28,
        self::PREF_calc_taxes_courtier                          => 29,
        self::PREF_calc_taxes_courtier_payees                   => 30,
        self::PREF_calc_taxes_courtier_solde                    => 31,
        self::PREF_calc_taxes_assureurs_tab                     => 32,
        self::PREF_calc_taxes_assureurs                         => 33,
        self::PREF_calc_taxes_assureurs_payees                  => 34,
        self::PREF_calc_taxes_assureurs_solde                   => 35,
        self::PREF_calc_sinistre_dommage_total                  => 36,
        self::PREF_calc_sinistre_indemnisation_total            => 37,
        self::PREF_calc_sinistre_indice_SP                      => 38,
        self::PREF_PRO_PRODUIT_IARD                             => 39
    ];

    //FINANCE - TAXES
    public const PREF_FIN_TAXE_ID                       = "Id";
    public const PREF_FIN_TAXE_NOM                      = "Nom";
    public const PREF_FIN_TAXE_DESCRIPTION              = "Description";
    public const PREF_FIN_TAXE_TAUX_IARD                = "Taux (IARD/Non Vie)";
    public const PREF_FIN_TAXE_TAUX_VIE                 = "Taux (Vie)";
    public const PREF_FIN_TAXE_ORGANISATION             = "Organisation";
    public const PREF_FIN_TAXE_PAR_COURTIER             = "Par Courtier?";
    public const PREF_FIN_TAXE_UTILISATEUR              = "Utilisateur";
    public const PREF_FIN_TAXE_ENTREPRISE               = "Entreprise";
    public const PREF_FIN_TAXE_DATE_DE_CREATION         = "Date de création";
    public const PREF_FIN_TAXE_DERNIERE_MODIFICATION    = "Dernière modification";
    public const TAB_FIN_TAXES = [
        self::PREF_FIN_TAXE_ID                                  => 0,
        self::PREF_FIN_TAXE_NOM                                 => 1,
        self::PREF_FIN_TAXE_DESCRIPTION                         => 2,
        self::PREF_FIN_TAXE_TAUX_IARD                           => 3,
        self::PREF_FIN_TAXE_TAUX_VIE                            => 4,
        self::PREF_FIN_TAXE_ORGANISATION                        => 5,
        self::PREF_FIN_TAXE_PAR_COURTIER                        => 6,
        self::PREF_FIN_TAXE_UTILISATEUR                         => 7,
        self::PREF_FIN_TAXE_ENTREPRISE                          => 8,
        self::PREF_FIN_TAXE_DATE_DE_CREATION                    => 9,
        self::PREF_FIN_TAXE_DERNIERE_MODIFICATION               => 10,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_taxes_courtier                          => 11,
        self::PREF_calc_taxes_courtier_payees                   => 12,
        self::PREF_calc_taxes_courtier_solde                    => 13,
        self::PREF_calc_taxes_assureurs                         => 14,
        self::PREF_calc_taxes_assureurs_payees                  => 15,
        self::PREF_calc_taxes_assureurs_solde                   => 16

    ];

    //FINANCE - MONNAIE
    public const PREF_FIN_MONNAIE_ID                    = "Id";
    public const PREF_FIN_MONNAIE_NOM                   = "Nom de la monnaie";
    public const PREF_FIN_MONNAIE_CODE                  = "Code";
    public const PREF_FIN_MONNAIE_TAUX_USD              = "Taux (en USD)";
    public const PREF_FIN_MONNAIE_FONCTION              = "Fonction Système";
    public const PREF_FIN_MONNAIE_IS_LOCALE             = "Locale?";
    public const PREF_FIN_MONNAIE_UTILISATEUR           = "Utilisateur";
    public const PREF_FIN_MONNAIE_ENTREPRISE            = "Entreprise";
    public const PREF_FIN_MONNAIE_DATE_DE_CREATION      = "Date de création";
    public const PREF_FIN_MONNAIE_DERNIRE_MODIFICATION  = "Dernière modification";
    public const TAB_FIN_MONNAIES = [
        self::PREF_FIN_MONNAIE_ID                               => 0,
        self::PREF_FIN_MONNAIE_NOM                              => 1,
        self::PREF_FIN_MONNAIE_CODE                             => 2,
        self::PREF_FIN_MONNAIE_FONCTION                         => 3,
        self::PREF_FIN_MONNAIE_TAUX_USD                         => 4,
        self::PREF_FIN_MONNAIE_IS_LOCALE                        => 5,
        self::PREF_FIN_MONNAIE_UTILISATEUR                      => 6,
        self::PREF_FIN_MONNAIE_ENTREPRISE                       => 7,
        self::PREF_FIN_MONNAIE_DATE_DE_CREATION                 => 8,
        self::PREF_FIN_MONNAIE_DERNIRE_MODIFICATION             => 9
    ];

    //FINANCE - FACTURE
    public const PREF_FIN_FACTURE_ID                    = "Id";
    public const PREF_FIN_FACTURE_ELEMENTS              = "Eléments facturés";
    public const PREF_FIN_FACTURE_COMPTES_BANCIARES     = "Comptes Bancaires";
    public const PREF_FIN_FACTURE_REFERENCE             = "Référence";
    public const PREF_FIN_FACTURE_SIGNED_BY             = "Signée par";
    public const PREF_FIN_FACTURE_POSTE_SIGNED_BY       = "Poste";
    public const PREF_FIN_FACTURE_UTILISATEUR           = "Utilisateur";
    public const PREF_FIN_FACTURE_ENTREPRISE            = "Entreprise";
    public const PREF_FIN_FACTURE_DATE_DE_CREATION      = "Date de création";
    public const PREF_FIN_FACTURE_DERNIRE_MODIFICATION  = "Dernière modification";
    public const PREF_FIN_FACTURE_TYPE                   = "Type de note";
    public const PREF_FIN_FACTURE_ASSUREUR              = "Assureur";
    public const PREF_FIN_FACTURE_PARTENAIRE            = "Partenaire";
    public const PREF_FIN_FACTURE_AUTRE_TIERS           = "Tiers Concerné";
    public const PREF_FIN_FACTURE_DESCRIPTION           = "Description";
    public const PREF_FIN_FACTURE_PIECE                 = "Pièce justificative";
    public const PREF_FIN_FACTURE_TOTAL_DU              = "Total Du";
    public const PREF_FIN_FACTURE_TOTAL_RECU            = "Total Reçu";
    public const PREF_FIN_FACTURE_TOTAL_SOLDE           = "Total Solde";
    public const PREF_FIN_FACTURE_STATUS                = "Status";
    public const PREF_FIN_FACTURE_PAIEMENTS             = "Paiements";

    public const TAB_FIN_FACTURE = [
        self::PREF_FIN_FACTURE_ID                       => 0,
        self::PREF_FIN_FACTURE_ELEMENTS                 => 1,
        self::PREF_FIN_FACTURE_REFERENCE                => 2,
        self::PREF_FIN_FACTURE_UTILISATEUR              => 3,
        self::PREF_FIN_FACTURE_ENTREPRISE               => 4,
        self::PREF_FIN_FACTURE_DATE_DE_CREATION         => 5,
        self::PREF_FIN_FACTURE_DERNIRE_MODIFICATION     => 6,
        self::PREF_FIN_FACTURE_TYPE                     => 7,
        self::PREF_FIN_FACTURE_ASSUREUR                 => 8,
        self::PREF_FIN_FACTURE_PARTENAIRE               => 9,
        self::PREF_FIN_FACTURE_DESCRIPTION              => 10,
        self::PREF_FIN_FACTURE_PIECE                    => 11,
        self::PREF_FIN_FACTURE_TOTAL_DU                 => 15,
        self::PREF_FIN_FACTURE_TOTAL_RECU               => 16,
        self::PREF_FIN_FACTURE_AUTRE_TIERS              => 17,
        self::PREF_FIN_FACTURE_COMPTES_BANCIARES        => 18,
        self::PREF_FIN_FACTURE_SIGNED_BY                => 19,
        self::PREF_FIN_FACTURE_POSTE_SIGNED_BY          => 20,
        self::PREF_FIN_FACTURE_TOTAL_SOLDE              => 21,
        self::PREF_FIN_FACTURE_STATUS                   => 22,
        self::PREF_FIN_FACTURE_PAIEMENTS                => 23,

    ];

    //COMPTE BANCAIRE
    public const PREF_FIN_COMPTE_BANCAIRE_ID = "Id";
    public const PREF_FIN_COMPTE_BANCAIRE_INTITULE = "Intitulé du Compte";
    public const PREF_FIN_COMPTE_BANCAIRE_NUMERO = "Numéro du Compte";
    public const PREF_FIN_COMPTE_BANCAIRE_BANQUE = "Banque";
    public const PREF_FIN_COMPTE_BANCAIRE_CODESWIFT = "Code Swift";
    public const PREF_FIN_COMPTE_BANCAIRE_MONNAIE = "Dévise";
    public const PREF_FIN_COMPTE_BANCAIRE_ENTREPRISE = "Entreprise";
    public const PREF_FIN_COMPTE_BANCAIRE_UTILISATEUR = "Utilisateur";
    public const PREF_FIN_COMPTE_BANCAIRE_DATE_CREATION = "Date de création";
    public const PREF_FIN_COMPTE_BANCAIRE_DATE_MODIFICATION = "Dernière modification";

    public const TAB_FIN_COMPTE_BANCAIRE = [
        self::PREF_FIN_COMPTE_BANCAIRE_ID => 0,
        self::PREF_FIN_COMPTE_BANCAIRE_INTITULE => 1,
        self::PREF_FIN_COMPTE_BANCAIRE_NUMERO => 2,
        self::PREF_FIN_COMPTE_BANCAIRE_BANQUE => 3,
        self::PREF_FIN_COMPTE_BANCAIRE_CODESWIFT => 4,
        self::PREF_FIN_COMPTE_BANCAIRE_MONNAIE => 5,
        self::PREF_FIN_COMPTE_BANCAIRE_ENTREPRISE => 6,
        self::PREF_FIN_COMPTE_BANCAIRE_UTILISATEUR => 7,
        self::PREF_FIN_COMPTE_BANCAIRE_DATE_CREATION => 8,
        self::PREF_FIN_COMPTE_BANCAIRE_DATE_MODIFICATION => 9,
    ];


    //PAIEMENT
    public const PREF_FIN_PAIEMENT_ID = "Id";
    public const PREF_FIN_PAIEMENT_TYPE = "Type";
    public const PREF_FIN_PAIEMENT_DATE = "Date de paiement";
    public const PREF_FIN_PAIEMENT_MONTANT = "Montant";
    public const PREF_FIN_PAIEMENT_DESCRIPTION = "Description";
    public const PREF_FIN_PAIEMENT_PIECE = "Pièces";
    public const PREF_FIN_PAIEMENT_COMPTE_BANCAIRE = "Compte bancaire";
    public const PREF_FIN_PAIEMENT_FACTURE = "Facture";
    public const PREF_FIN_PAIEMENT_ENTREPRISE = "Entreprise";
    public const PREF_FIN_PAIEMENT_UTILISATEUR = "Utilisateur";
    public const PREF_FIN_PAIEMENT_DATE_CREATION = "Date de création";
    public const PREF_FIN_PAIEMENT_DATE_MODIFICATION = "Dernière modification";

    public const TAB_FIN_PAIEMENT = [
        self::PREF_FIN_PAIEMENT_ID => 0,
        self::PREF_FIN_PAIEMENT_DATE => 1,
        self::PREF_FIN_PAIEMENT_MONTANT => 2,
        self::PREF_FIN_PAIEMENT_DESCRIPTION => 3,
        self::PREF_FIN_PAIEMENT_FACTURE => 4,
        self::PREF_FIN_PAIEMENT_ENTREPRISE => 5,
        self::PREF_FIN_PAIEMENT_UTILISATEUR => 6,
        self::PREF_FIN_PAIEMENT_DATE_CREATION => 7,
        self::PREF_FIN_PAIEMENT_DATE_MODIFICATION => 8,
        self::PREF_FIN_PAIEMENT_PIECE => 9,
        self::PREF_FIN_PAIEMENT_COMPTE_BANCAIRE => 10,
        self::PREF_FIN_PAIEMENT_TYPE => 11,
    ];

    //FINANCE - FACTURE
    public const PREF_FIN_ELEMENT_FACTURE_ID                = "Id";
    public const PREF_FIN_ELEMENT_FACTURE_POLICE            = "Police";
    public const PREF_FIN_ELEMENT_FACTURE_FACTURE           = "Facture";
    public const PREF_FIN_ELEMENT_FACTURE_MONTANT           = "Montant à payer";
    public const PREF_FIN_ELEMENT_FACTURE_ENTREPRISE        = "Entreprise";
    public const PREF_FIN_ELEMENT_FACTURE_UTILISATEUR       = "Utilisateur";
    public const PREF_FIN_ELEMENT_FACTURE_DATE_CREATION     = "Date de création";
    public const PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION = "Dernière Modification";

    public const TAB_FIN_ELEMENT_FACTURE = [
        self::PREF_FIN_ELEMENT_FACTURE_ID                => 0,
        self::PREF_FIN_ELEMENT_FACTURE_POLICE            => 1,
        self::PREF_FIN_ELEMENT_FACTURE_FACTURE           => 2,
        self::PREF_FIN_ELEMENT_FACTURE_MONTANT           => 3,
        self::PREF_FIN_ELEMENT_FACTURE_ENTREPRISE        => 4,
        self::PREF_FIN_ELEMENT_FACTURE_UTILISATEUR       => 5,
        self::PREF_FIN_ELEMENT_FACTURE_DATE_CREATION     => 6,
        self::PREF_FIN_ELEMENT_FACTURE_DATE_MODIFICATION => 7
    ];

    //SINISTRE - ETAPE
    public const PREF_SIN_ETAPE_ID                      = "Id";
    public const PREF_SIN_ETAPE_NOM                     = "Intitulé de l'étape";
    public const PREF_SIN_ETAPE_DESCRIPTION             = "Description";
    public const PREF_SIN_ETAPE_SINISTRES               = "Sinistres";
    public const PREF_SIN_ETAPE_INDICE                  = "Indice";
    public const PREF_SIN_ETAPE_UTILISATEUR             = "Utilisateur";
    public const PREF_SIN_ETAPE_ENTREPRISE              = "Entreprise";
    public const PREF_SIN_ETAPE_DATE_DE_CREATION        = "Date de création";
    public const PREF_SIN_ETAPE_DERNIRE_MODIFICATION    = "Dernière modification";
    public const TAB_SIN_ETAPES = [
        self::PREF_SIN_ETAPE_ID                     => 0,
        self::PREF_SIN_ETAPE_NOM                    => 1,
        self::PREF_SIN_ETAPE_DESCRIPTION            => 2,
        self::PREF_SIN_ETAPE_SINISTRES              => 3,
        self::PREF_SIN_ETAPE_INDICE                 => 4,
        self::PREF_SIN_ETAPE_UTILISATEUR            => 5,
        self::PREF_SIN_ETAPE_ENTREPRISE             => 6,
        self::PREF_SIN_ETAPE_DATE_DE_CREATION       => 7,
        self::PREF_SIN_ETAPE_DERNIRE_MODIFICATION   => 8
    ];
    //SINISTRE - EXPERT
    public const PREF_SIN_EXPERT_ID                     = "Id";
    public const PREF_SIN_EXPERT_NOM                    = "Nom";
    public const PREF_SIN_EXPERT_ADRESSE                = "Adresse";
    public const PREF_SIN_EXPERT_SITE_INTERNET          = "Site Internet";
    public const PREF_SIN_EXPERT_EMAIL                  = "Email";
    public const PREF_SIN_EXPERT_TELEPHONE              = "Téléphone";
    public const PREF_SIN_EXPERT_DESCRIPTION            = "Description";
    public const PREF_SIN_EXPERT_SINISTRES              = "Sinistres";
    public const PREF_SIN_EXPERT_UTILISATEUR            = "Utilisateur";
    public const PREF_SIN_EXPERT_ENTREPRISE             = "Entreprise";
    public const PREF_SIN_EXPERT_DATE_DE_CREATION       = "Date de création";
    public const PREF_SIN_EXPERT_DERNIRE_MODIFICATION   = "Dernière modification";
    public const TAB_SIN_EXPERTS = [
        self::PREF_SIN_EXPERT_ID                    => 0,
        self::PREF_SIN_EXPERT_NOM                   => 1,
        self::PREF_SIN_EXPERT_ADRESSE               => 2,
        self::PREF_SIN_EXPERT_SITE_INTERNET         => 3,
        self::PREF_SIN_EXPERT_EMAIL                 => 4,
        self::PREF_SIN_EXPERT_TELEPHONE             => 5,
        self::PREF_SIN_EXPERT_DESCRIPTION           => 6,
        self::PREF_SIN_EXPERT_SINISTRES             => 7,
        self::PREF_SIN_EXPERT_UTILISATEUR           => 8,
        self::PREF_SIN_EXPERT_ENTREPRISE            => 9,
        self::PREF_SIN_EXPERT_DATE_DE_CREATION      => 10,
        self::PREF_SIN_EXPERT_DERNIRE_MODIFICATION  => 11
    ];
    //SINISTRE - SINISTRE
    public const PREF_SIN_SINISTRE_ID                   = "Id";
    public const PREF_SIN_SINISTRE_REFERENCE            = "Numéro Sinistre";
    public const PREF_SIN_SINISTRE_ITITRE               = "Intitulé du sinistre";
    public const PREF_SIN_SINISTRE_DATE_OCCURENCE       = "Date de l'incident";
    public const PREF_SIN_SINISTRE_DESCRIPTION          = "Description";
    public const PREF_SIN_SINISTRE_VICTIMES             = "Victimes";
    public const PREF_SIN_SINISTRE_ACTIONS              = "Missions";
    public const PREF_SIN_SINISTRE_EXPERT               = "Experts";
    public const PREF_SIN_SINISTRE_POLICE               = "Police";
    public const PREF_SIN_SINISTRE_COUT                 = "Coût de réparation";
    public const PREF_SIN_SINISTRE_MONTANT_PAYE         = "Compensation";
    public const PREF_SIN_SINISTRE_DATE_PAIEMENT        = "Date de paiement";
    public const PREF_SIN_SINISTRE_ETAPE                = "Etape actuelle";
    public const PREF_SIN_SINISTRE_DOCUMENTS            = "Documents";
    public const PREF_SIN_SINISTRE_UTILISATEUR          = "Utilisateur";
    public const PREF_SIN_SINISTRE_ENTREPRISE           = "Entreprise";
    public const PREF_SIN_SINISTRE_DATE_DE_CREATION     = "Date de création";
    public const PREF_SIN_SINISTRE_DERNIRE_MODIFICATION = "Dernière modification";
    public const TAB_SIN_SINISTRES = [
        self::PREF_SIN_SINISTRE_ID                              => 0,
        self::PREF_SIN_SINISTRE_REFERENCE                       => 1,
        self::PREF_SIN_SINISTRE_ITITRE                          => 2,
        self::PREF_SIN_SINISTRE_DATE_OCCURENCE                  => 3,
        self::PREF_SIN_SINISTRE_DESCRIPTION                     => 4,
        self::PREF_SIN_SINISTRE_VICTIMES                        => 5,
        self::PREF_SIN_SINISTRE_ACTIONS                         => 6,
        self::PREF_SIN_SINISTRE_EXPERT                          => 7,
        self::PREF_SIN_SINISTRE_POLICE                          => 8,
        self::PREF_SIN_SINISTRE_COUT                            => 9,
        self::PREF_SIN_SINISTRE_MONTANT_PAYE                    => 10,
        self::PREF_SIN_SINISTRE_DATE_PAIEMENT                   => 11,
        self::PREF_SIN_SINISTRE_ETAPE                           => 12,
        self::PREF_SIN_SINISTRE_DOCUMENTS                       => 13,
        self::PREF_SIN_SINISTRE_UTILISATEUR                     => 14,
        self::PREF_SIN_SINISTRE_ENTREPRISE                      => 15,
        self::PREF_SIN_SINISTRE_DATE_DE_CREATION                => 16,
        self::PREF_SIN_SINISTRE_DERNIRE_MODIFICATION            => 17,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_primes_nette                    => 18,
        self::PREF_calc_polices_primes_totale                   => 19,
        self::PREF_calc_polices_fronting                        => 20,
        self::PREF_calc_polices_accessoire                      => 21,
        self::PREF_calc_polices_tva                             => 22,
        self::PREF_calc_revenu_reserve                          => 23,
        self::PREF_calc_revenu_partageable                      => 24,
        self::PREF_calc_revenu_ht                               => 25,
        self::PREF_calc_revenu_ttc                              => 26,
        self::PREF_calc_revenu_ttc_encaisse                     => 27,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 28,
        self::PREF_calc_retrocom                                => 29,
        self::PREF_calc_retrocom_payees                         => 30,
        self::PREF_calc_retrocom_solde                          => 31,
        self::PREF_calc_taxes_courtier_tab                      => 32,
        self::PREF_calc_taxes_courtier                          => 33,
        self::PREF_calc_taxes_courtier_payees                   => 34,
        self::PREF_calc_taxes_courtier_solde                    => 35,
        self::PREF_calc_taxes_assureurs_tab                     => 36,
        self::PREF_calc_taxes_assureurs                         => 37,
        self::PREF_calc_taxes_assureurs_payees                  => 38,
        self::PREF_calc_taxes_assureurs_solde                   => 39,
        self::PREF_calc_sinistre_dommage_total                  => 40,
        self::PREF_calc_sinistre_indemnisation_total            => 41,
        self::PREF_calc_sinistre_indice_SP                      => 42
    ];

    //SINISTRE - VICTIME
    public const PREF_SIN_VICTIME_ID                    = "Id";
    public const PREF_SIN_VICTIME_NOM                   = "Nom comptet";
    public const PREF_SIN_VICTIME_ADRESSE               = "Adresse";
    public const PREF_SIN_VICTIME_TELEPHONE             = "Téléphone";
    public const PREF_SIN_VICTIME_EMAIL                 = "Email";
    public const PREF_SIN_VICTIME_SINISTRE              = "Sinistre";
    public const PREF_SIN_VICTIME_UTILISATEUR           = "Utilisateur";
    public const PREF_SIN_VICTIME_ENTREPRISE            = "Entreprise";
    public const PREF_SIN_VICTIME_DATE_DE_CREATION      = "Date de création";
    public const PREF_SIN_VICTIME_DERNIRE_MODIFICATION  = "Dernière modification";
    public const TAB_SIN_VICTIMES = [
        self::PREF_SIN_VICTIME_ID                   => 0,
        self::PREF_SIN_VICTIME_NOM                  => 1,
        self::PREF_SIN_VICTIME_ADRESSE              => 2,
        self::PREF_SIN_VICTIME_TELEPHONE            => 3,
        self::PREF_SIN_VICTIME_EMAIL                => 4,
        self::PREF_SIN_VICTIME_SINISTRE             => 5,
        self::PREF_SIN_VICTIME_UTILISATEUR          => 6,
        self::PREF_SIN_VICTIME_ENTREPRISE           => 7,
        self::PREF_SIN_VICTIME_DATE_DE_CREATION     => 8,
        self::PREF_SIN_VICTIME_DERNIRE_MODIFICATION => 9
    ];
    //BIBLIOTHEQUE - CATEGORIE
    public const PREF_BIB_CATEGORIE_ID                      = "Id";
    public const PREF_BIB_CATEGORIE_NOM                     = "Nom";
    public const PREF_BIB_CATEGORIE_UTILISATEUR             = "Utilisateur";
    public const PREF_BIB_CATEGORIE_PIECES                  = "Pièces";
    public const PREF_BIB_CATEGORIE_ENTREPRISE              = "Entreprise";
    public const PREF_BIB_CATEGORIE_DATE_DE_CREATION        = "Date de création";
    public const PREF_BIB_CATEGORIE_DERNIRE_MODIFICATION    = "Dernière modification";
    public const TAB_BIB_CATEGORIES = [
        self::PREF_BIB_CATEGORIE_ID                     => 0,
        self::PREF_BIB_CATEGORIE_NOM                    => 1,
        self::PREF_BIB_CATEGORIE_UTILISATEUR            => 2,
        self::PREF_BIB_CATEGORIE_PIECES                 => 3,
        self::PREF_BIB_CATEGORIE_ENTREPRISE             => 4,
        self::PREF_BIB_CATEGORIE_DATE_DE_CREATION       => 5,
        self::PREF_BIB_CATEGORIE_DERNIRE_MODIFICATION   => 6
    ];
    //BIBLIOTHEQUE - CLASSEUR
    public const PREF_BIB_CLASSEUR_ID                   = "Id";
    public const PREF_BIB_CLASSEUR_NOM                  = "Nom";
    public const PREF_BIB_CLASSEUR_UTILISATEUR          = "Utilisateur";
    public const PREF_BIB_CLASSEUR_PIECES               = "Pièces";
    public const PREF_BIB_CLASSEUR_ENTREPRISE           = "Entreprise";
    public const PREF_BIB_CLASSEUR_DATE_DE_CREATION     = "Date de création";
    public const PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION = "Dernière modification";
    public const TAB_BIB_CLASSEURS = [
        self::PREF_BIB_CLASSEUR_ID                      => 0,
        self::PREF_BIB_CLASSEUR_NOM                     => 1,
        self::PREF_BIB_CLASSEUR_UTILISATEUR             => 2,
        self::PREF_BIB_CLASSEUR_PIECES                  => 3,
        self::PREF_BIB_CLASSEUR_ENTREPRISE              => 4,
        self::PREF_BIB_CLASSEUR_DATE_DE_CREATION        => 5,
        self::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION    => 6
    ];
    //BIBLIOTHEQUE - DOCUMENT
    public const PREF_BIB_DOCUMENT_ID                   = "Id";
    public const PREF_BIB_DOCUMENT_NOM                  = "Intitulé";
    public const PREF_BIB_DOCUMENT_TYPE                 = "Type";
    public const PREF_BIB_DOCUMENT_DESCRIPTION          = "Description";
    public const PREF_BIB_DOCUMENT_UTILISATEUR          = "Utilisateur";
    public const PREF_BIB_DOCUMENT_ENTREPRISE           = "Entreprise";
    public const PREF_BIB_DOCUMENT_DATE_DE_CREATION     = "Date de création";
    public const PREF_BIB_DOCUMENT_DERNIRE_MODIFICATION = "Dernière modification";
    public const TAB_BIB_DOCUMENTS = [
        self::PREF_BIB_DOCUMENT_ID                      => 0,
        self::PREF_BIB_DOCUMENT_NOM                     => 1,
        self::PREF_BIB_DOCUMENT_TYPE                    => 2,
        self::PREF_BIB_DOCUMENT_DESCRIPTION             => 3,
        self::PREF_BIB_DOCUMENT_UTILISATEUR             => 4,
        self::PREF_BIB_DOCUMENT_ENTREPRISE              => 5,
        self::PREF_BIB_DOCUMENT_DATE_DE_CREATION        => 6,
        self::PREF_BIB_DOCUMENT_DERNIRE_MODIFICATION    => 7,
    ];
    //PARAMETRES - UTILISATEUR
    public const PREF_PAR_UTILISATEUR_ID                    = "Id";
    public const PREF_PAR_UTILISATEUR_NOM                   = "Nom complet";
    public const PREF_PAR_UTILISATEUR_PSEUDO                = "Speudo";
    public const PREF_PAR_UTILISATEUR_EMAIL                 = "Email";
    public const PREF_PAR_UTILISATEUR_ROLES                 = "Rôles";
    public const PREF_PAR_UTILISATEUR_UTILISATEUR           = "Utilisateur";
    public const PREF_PAR_UTILISATEUR_MISSIONS              = "Missions";
    public const PREF_PAR_UTILISATEUR_ENTREPRISE            = "Entreprise";
    public const PREF_PAR_UTILISATEUR_DATE_DE_CREATION      = "Date de création";
    public const PREF_PAR_UTILISATEUR_DERNIRE_MODIFICATION  = "Dernière modification";
    public const TAB_PAR_UTILISATEURS = [
        self::PREF_PAR_UTILISATEUR_ID                   => 0,
        self::PREF_PAR_UTILISATEUR_NOM                  => 1,
        self::PREF_PAR_UTILISATEUR_PSEUDO               => 2,
        self::PREF_PAR_UTILISATEUR_EMAIL                => 3,
        self::PREF_PAR_UTILISATEUR_ROLES                => 4,
        self::PREF_PAR_UTILISATEUR_UTILISATEUR          => 5,
        self::PREF_PAR_UTILISATEUR_ENTREPRISE           => 6,
        self::PREF_PAR_UTILISATEUR_DATE_DE_CREATION     => 7,
        self::PREF_PAR_UTILISATEUR_DERNIRE_MODIFICATION => 8,
        self::PREF_PAR_UTILISATEUR_MISSIONS             => 9
    ];


    public const PREF_APPARENCE_CLAIRE = 'Mode sombre désactivé';
    public const PREF_APPARENCE_SOMBRE = 'Mode sombre activé';
    public const TAB_APPARENCES = [
        self::PREF_APPARENCE_CLAIRE     => 0,
        self::PREF_APPARENCE_SOMBRE     => 1
    ];
    public const PREF_UTILISATEUR = "Utilisateur";
    public const PREF_ENTREPRISE = "Entreprise";


    public const PREF_FIN_REVENU_ID                     = 'Id';
    public const PREF_FIN_REVENU_TYPE                   = "Type";
    public const PREF_FIN_REVENU_PARTAGEABLE            = "Partageable?";
    public const PREF_FIN_REVENU_TAXABLE                = "Taxable?";
    public const PREF_FIN_REVENU_PAR_TRANCHE            = "Ce revenu est payable par tranche selon les termes de paiement.";
    public const PREF_FIN_REVENU_PAR_CLIENT             = "Ce revenu est payable par le client, pas par l'assureur";
    public const PREF_FIN_REVENU_BASE                   = "Formule";
    public const PREF_FIN_REVENU_TAUX                   = "Taux";
    public const PREF_FIN_REVENU_MONTANT_FLAT           = "Montant fixe";
    public const PREF_FIN_REVENU_UTILISATEUR            = "Utilisateur";
    public const PREF_FIN_REVENU_ENTREPRISE             = "Entreprise";
    public const PREF_FIN_REVENU__DATE_CREATION         = "Date de création";
    public const PREF_FIN_REVENU_DERNIRE_MODIFICATION   = "Dernière modification";

    public const TAB_FIN_REVENU = [
        self::PREF_FIN_REVENU_ID                     => 0,
        self::PREF_FIN_REVENU_TYPE                   => 1,
        self::PREF_FIN_REVENU_PARTAGEABLE            => 2,
        self::PREF_FIN_REVENU_TAXABLE                => 3,
        self::PREF_FIN_REVENU_BASE                   => 4,
        self::PREF_FIN_REVENU_TAUX                   => 5,
        self::PREF_FIN_REVENU_MONTANT_FLAT           => 6,
        self::PREF_FIN_REVENU_UTILISATEUR            => 7,
        self::PREF_FIN_REVENU_ENTREPRISE             => 8,
        self::PREF_FIN_REVENU__DATE_CREATION         => 9,
        self::PREF_FIN_REVENU_DERNIRE_MODIFICATION   => 10,
        self::PREF_FIN_REVENU_PAR_TRANCHE            => 11,
        self::PREF_FIN_REVENU_PAR_CLIENT             => 12,
    ];


    public const PREF_PROD_CHARGEMENT_ID                     = 'Id';
    public const PREF_PROD_CHARGEMENT_TYPE                   = "Type";
    public const PREF_PROD_CHARGEMENT_MONTANT                = "Montant";
    public const PREF_PROD_CHARGEMENT_DESCRIPTION            = "Description";
    public const PREF_PROD_CHARGEMENT_COTATION               = "Cotation";
    public const PREF_PROD_CHARGEMENT_UTILISATEUR            = "Utilisateur";
    public const PREF_PROD_CHARGEMENT_ENTREPRISE             = "Entreprise";
    public const PREF_PROD_CHARGEMENT_DATE_CREATION          = "Date de création";
    public const PREF_PROD_CHARGEMENT_DERNIRE_MODIFICATION   = "Dernière modification";

    public const TAB_PROD_CHARGEMENT = [
        self::PREF_PROD_CHARGEMENT_ID                   => 0,
        self::PREF_PROD_CHARGEMENT_TYPE                 => 1,
        self::PREF_PROD_CHARGEMENT_MONTANT              => 2,
        self::PREF_PROD_CHARGEMENT_DESCRIPTION          => 3,
        self::PREF_PROD_CHARGEMENT_COTATION             => 4,
        self::PREF_PROD_CHARGEMENT_UTILISATEUR          => 5,
        self::PREF_PROD_CHARGEMENT_ENTREPRISE           => 6,
        self::PREF_PROD_CHARGEMENT_DATE_CREATION        => 7,
        self::PREF_PROD_CHARGEMENT_DERNIRE_MODIFICATION => 8
    ];

    public const PREF_PROD_TRANCHE_ID                   = 'Id';
    public const PREF_PROD_TRANCHE_COTATION             = "Cotation";
    public const PREF_PROD_TRANCHE_POLICE               = "Police";
    public const PREF_PROD_TRANCHE_NOM                  = "Nom";
    public const PREF_PROD_TRANCHE_TAUX                 = "Taux";
    public const PREF_PROD_TRANCHE_DUREE                = "Durée (mois)";
    public const PREF_PROD_TRANCHE_MONTANT              = "Montant";
    public const PREF_PROD_TRANCHE_DEBUT                = "Début";
    public const PREF_PROD_TRANCHE_FIN                  = "Echéance";
    public const PREF_PROD_TRANCHE_UTILISATEUR          = "Utilisateur";
    public const PREF_PROD_TRANCHE_ENTREPRISE           = "Entreprise";
    public const PREF_PROD_TRANCHE_DATE_CREATION        = "Date de création";
    public const PREF_PROD_TRANCHE_DERNIRE_MODIFICATION = "D. modification";

    public const TAB_PROD_TRANCHE = [
        self::PREF_PROD_TRANCHE_ID                      => 0,
        self::PREF_PROD_TRANCHE_NOM                     => 1,
        self::PREF_PROD_TRANCHE_TAUX                    => 2,
        self::PREF_PROD_TRANCHE_MONTANT                 => 3,
        self::PREF_PROD_TRANCHE_DEBUT                   => 4,
        self::PREF_PROD_TRANCHE_FIN                     => 5,
        self::PREF_PROD_TRANCHE_UTILISATEUR             => 6,
        self::PREF_PROD_TRANCHE_ENTREPRISE              => 7,
        self::PREF_PROD_TRANCHE_DATE_CREATION           => 8,
        self::PREF_PROD_TRANCHE_DERNIRE_MODIFICATION    => 9,
        self::PREF_PROD_TRANCHE_COTATION                => 10,
        self::PREF_PROD_TRANCHE_POLICE                  => 11,
    ];


    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise,
        private ServicePreferences $servicePreferences
    ) {
        //AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em

    }

    public static function getEntityFqcn(): string
    {
        return Preference::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $connected_entreprise = $this->serviceEntreprise->getEntreprise();
        $hasVisionGlobale = $this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE]);
        $defaultQueryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if ($hasVisionGlobale == false) {
            $defaultQueryBuilder
                ->Where('entity.utilisateur = :user')
                ->setParameter('user', $this->getUser());
        }
        return $defaultQueryBuilder
            ->andWhere('entity.entreprise = :ese')
            ->setParameter('ese', $connected_entreprise);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDateTimeFormat('dd/MM/yyyy à HH:mm:ss')
            ->setDateFormat('dd/MM/yyyy')
            ->setPaginatorPageSize(100)
            ->renderContentMaximized()
            ->setEntityLabelInSingular("Paramètres d'affichage")
            ->setEntityLabelInPlural("Paramètres d'affichage")
            ->setPageTitle("index", "Liste des préférences")
            ->setDefaultSort(['updatedAt' => 'DESC'])
            ->setEntityPermission(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::ACCES_PRODUCTION])
            // ...
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        if ($this->isGranted(UtilisateurCrudController::TAB_ROLES[UtilisateurCrudController::VISION_GLOBALE])) {
            $filters->add('utilisateur');
        }
        return $filters;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            //Onglet 01 - Généralités
            FormField::addTab(' GENERALITES')
                ->setIcon('fas fa-file-shield')
                ->setHelp("Les paramètres qui s'appliquent sur toutes les rubriques de l'espade de travail."),

            ChoiceField::new('apparence', "Arrière-plan Sombre")
                ->renderExpanded()
                ->setChoices(self::TAB_APPARENCES)
                ->renderAsBadges([
                    self::PREF_APPARENCE_SOMBRE => 'success', //info
                    self::PREF_APPARENCE_CLAIRE => 'danger',
                ]),
            AssociationField::new('utilisateur', self::PREF_UTILISATEUR)
                ->onlyOnDetail()
                ->setFormTypeOption('query_builder', function (EntityRepository $entityRepository) {
                    return $entityRepository
                        ->createQueryBuilder('e')
                        ->Where('e.entreprise = :ese')
                        ->setParameter('ese', $this->serviceEntreprise->getEntreprise());
                }),
            AssociationField::new('entreprise', self::PREF_ENTREPRISE)->onlyOnDetail(),
            DateTimeField::new('createdAt', "Date de création")->onlyOnDetail(), //->setColumns(2),
            DateTimeField::new('updatedAt', "Date de modification")->onlyOnDetail(), //->setColumns(2),


            //Onglet 02 - CRM
            FormField::addTab(' COMMERCIAL / CRM')
                ->setIcon('fas fa-bullseye')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section CRM."),
            NumberField::new('crmTaille', "Eléments par page"), //->setColumns(2), //->setColumns(3),
            ChoiceField::new('crmPistes', "Piste")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_PISTE),
            ChoiceField::new('crmMissions', "Mission")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_MISSIONS),
            ChoiceField::new('crmFeedbacks', "Feedback")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_FEEDBACKS),
            ChoiceField::new('crmCotations', "Cotations")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_COTATIONS),
            ChoiceField::new('crmEtapes', "Etapes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_ETAPES),

            //Onglet 03 - PRODUCTION
            FormField::addTab(' PRODUCTION')
                ->setIcon('fas fa-bag-shopping')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section PRODUCTION."),
            NumberField::new('proTaille', "Eléments par page"), //->setColumns(2),
            ChoiceField::new('proAssureurs', "Assureur")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_ASSUREURS),
            ChoiceField::new('proPolices', "Polices")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_POLICES),
            ChoiceField::new('proProduits', "Produits")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_PRODUITS),
            ChoiceField::new('proClients', "Clients")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_CLIENTS),
            ChoiceField::new('proPartenaires', "Partenaires")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_PARTENAIRES),
            ChoiceField::new('proAutomobiles', "Engins")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_ENGINS),
            ChoiceField::new('proContacts', "Contact")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_CONTACTS),

            //Onglet 04 - FINANCES
            FormField::addTab(' FINANCES')
                ->setIcon('fas fa-sack-dollar')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section FINANCES."),
            NumberField::new('finTaille', "Eléments par page"), //->setColumns(12),
            ChoiceField::new('finTaxes', "Taxes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_TAXES),
            ChoiceField::new('finMonnaies', "Monnaies")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_MONNAIES),
            ChoiceField::new('finFactures', "Factures")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_FACTURE),
            ChoiceField::new('finElementFactures', "Eléments de Facture")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_ELEMENT_FACTURE),

            //Onglet 05 - SINISTRE
            FormField::addTab(' SINISTRE')
                ->setIcon('fas fa-fire')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section SINISTRE."),
            NumberField::new('sinTaille', "Eléments par page"), //->setColumns(2),
            ChoiceField::new('sinSinistres', "Sinistres")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_SINISTRES),
            ChoiceField::new('sinEtapes', "Etapes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_ETAPES),
            ChoiceField::new('sinExperts', "Experts")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_EXPERTS),
            ChoiceField::new('sinVictimes', "Victimes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_VICTIMES),


            //Onglet 06 - BIBLIOTHEQUE
            FormField::addTab(' BIBLIOTHEQUE')
                ->setIcon('fas fa-book')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section BIBLIOTHEQUE."),
            NumberField::new('bibTaille', "Eléments par page"), //->setColumns(2),
            ChoiceField::new('bibCategories', "Catégories")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_BIB_CATEGORIES),
            ChoiceField::new('bibClasseurs', "Classeurs")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_BIB_CLASSEURS),
            ChoiceField::new('bibPieces', "Documents")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_BIB_DOCUMENTS),

            //Onglet 07 - PARAMETRES
            FormField::addTab(' PARAMETRES')
                ->setIcon('fas fa-gears')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section PARAMETRES."),
            NumberField::new('parTaille', "Eléments par page"), //->setColumns(2),
            ChoiceField::new('parUtilisateurs', "Utilisateurs")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PAR_UTILISATEURS),

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $reinitialiser = Action::new(DashboardController::ACTION_RESET)
            ->setIcon('fa-solid fa-hammer') //<i class="fa-solid fa-hammer"></i>
            ->linkToCrudAction('resetEntite');

        return $actions
            //quelques boutons
            ->add(Crud::PAGE_DETAIL, $reinitialiser)
            ->add(Crud::PAGE_EDIT, $reinitialiser)
            //->add(Crud::PAGE_INDEX, $reinitialiser)

            //les Updates sur la page détail
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })

            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN);
    }

    public function resetEntite(AdminContext $context, AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $em)
    {
        /**@var Assureur $assureur */
        $entite = $context->getEntity()->getInstance();

        //On reinitialise les préférences de cet utilisateur
        $this->servicePreferences->resetPreferences(
            $this->serviceEntreprise->getUtilisateur(),
            $this->serviceEntreprise->getEntreprise()
        );

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($entite->getId())
            ->generateUrl();

        $this->addFlash("success", "Salut " . $this->serviceEntreprise->getUtilisateur() . ", La reinitialisation de vos paramètres d'affichage est effectuée avec succès.");

        return $this->redirect($url);
    }
}
