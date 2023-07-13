<?php

namespace App\Controller\Admin;

use App\Entity\Preference;
use App\Service\ServiceEntreprise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class PreferenceCrudController extends AbstractCrudController
{

    //CHAMPS CALCULABLES AUTOMATIQUEMENT
    public const PREF_calc_polices_tab                                  = 'Ac/Polices/Réf.';
    public const PREF_calc_polices_primes_nette                         = 'Ac/Prime/Mnt ht';
    public const PREF_calc_polices_primes_totale                        = 'Ac/Prime/Mnt Total';
    public const PREF_calc_polices_fronting                             = 'Ac/Prime/Fronting';
    public const PREF_calc_polices_accessoire                           = 'Ac/Prime/Accessoires';
    public const PREF_calc_polices_tva                                  = 'Ac/Prime/Taxes';
    public const PREF_calc_revenu_reserve                               = 'Ac/Comm./Réserve';
    public const PREF_calc_revenu_partageable                           = 'Ac/Comm./A partager';
    public const PREF_calc_revenu_ht                                    = 'Ac/Comm./Mnt ht';
    public const PREF_calc_revenu_ttc                                   = 'Ac/Comm./Mnt dû';
    public const PREF_calc_revenu_ttc_encaisse                          = 'Ac/Comm./Pymnt';
    public const PREF_calc_revenu_ttc_encaisse_tab_ref_factures         = 'Ac/Comm./PdP';
    public const PREF_calc_revenu_ttc_solde_restant_du                  = 'Ac/Comm./Solde dû';
    public const PREF_calc_retrocom                                     = 'Ac/Retrocom./Mnt dû';
    public const PREF_calc_retrocom_payees                              = 'Ac/Retrocom./Pymnt';
    public const PREF_calc_retrocom_payees_tab_factures                 = 'Ac/Retrocom./PdP';
    public const PREF_calc_retrocom_solde                               = 'Ac/Retrocom./Solde';
    public const PREF_calc_taxes_courtier_tab                           = 'Ac/Taxes/Court./Réf.';
    public const PREF_calc_taxes_courtier                               = 'Ac/Taxes/Court./Mnt dû';
    public const PREF_calc_taxes_courtier_payees                        = 'Ac/Taxes/Court./Pymnt';
    public const PREF_calc_taxes_courtier_payees_tab_ref_factures       = 'Ac/Taxes/Court./PdP';
    public const PREF_calc_taxes_courtier_solde                         = 'Ac/Taxes/Court./Solde';
    public const PREF_calc_taxes_assureurs_tab                          = 'Ac/Taxes/Assur./Réf.';
    public const PREF_calc_taxes_assureurs                              = 'Ac/Taxes/Assur./Mnt dû';
    public const PREF_calc_taxes_assureurs_payees                       = 'Ac/Taxes/Assur./Pymnt';
    public const PREF_calc_taxes_assureurs_payees_tab_ref_factures      = 'Ac/Taxes/Assur./PdP';
    public const PREF_calc_taxes_assureurs_solde                        = 'Ac/Taxes/Assur./Solde';


    //CRM - ACTION / MISSION
    public const PREF_CRM_MISSION_ID                = "Id";
    public const PREF_CRM_MISSION_NOM               = "Nom";
    public const PREF_CRM_MISSION_OBJECTIF          = "Objectif";
    public const PREF_CRM_MISSION_STATUS            = "Status";
    public const PREF_CRM_MISSION_PISTE             = "Piste";
    public const PREF_CRM_MISSION_STARTED_AT        = "Date d'effet";
    public const PREF_CRM_MISSION_ENDED_AT          = "Echéance";
    public const PREF_CRM_MISSION_ATTRIBUE_A        = "Attribuée à";
    public const PREF_CRM_MISSION_UTILISATEUR       = "Utilisateur";
    public const PREF_CRM_MISSION_ENTREPRISE        = "Entreprise";
    public const PREF_CRM_MISSION_CREATED_AT        = "Date de création";
    public const PREF_CRM_MISSION_UPDATED_AT        = "Dernière modification";
    public const TAB_CRM_MISSIONS = [
        self::PREF_CRM_MISSION_ID           => 0,
        self::PREF_CRM_MISSION_NOM          => 1,
        self::PREF_CRM_MISSION_OBJECTIF     => 2,
        self::PREF_CRM_MISSION_STATUS       => 3,
        self::PREF_CRM_MISSION_PISTE        => 4,
        self::PREF_CRM_MISSION_STARTED_AT   => 5,
        self::PREF_CRM_MISSION_ENDED_AT     => 6,
        self::PREF_CRM_MISSION_ATTRIBUE_A   => 7,
        self::PREF_CRM_MISSION_UTILISATEUR  => 8,
        self::PREF_CRM_MISSION_ENTREPRISE   => 9,
        self::PREF_CRM_MISSION_CREATED_AT   => 10,
        self::PREF_CRM_MISSION_UPDATED_AT   => 11
    ];

    //CRM - FEEDBACK
    public const PREF_CRM_FEEDBACK_ID                   = "Id";
    public const PREF_CRM_FEEDBACK_MESAGE               = "Message";
    public const PREF_CRM_FEEDBACK_PROCHAINE_ETAPE      = "Etape suivante";
    public const PREF_CRM_FEEDBACK_DATE_EFFET           = "Date d'effet";
    public const PREF_CRM_FEEDBACK_ACTION               = "Action";
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
    public const PREF_CRM_COTATION_NOM                  = "Nom";
    public const PREF_CRM_COTATION_ASSUREUR             = "Assureur";
    public const PREF_CRM_COTATION_MONNAIE              = "Monnaie";
    public const PREF_CRM_COTATION_PRIME_TOTALE         = "Prime totale";
    public const PREF_CRM_COTATION_RISQUE               = "Risque";
    public const PREF_CRM_COTATION_PISTE                = "Piste";
    public const PREF_CRM_COTATION_PIECES               = "Pièces";
    public const PREF_CRM_COTATION_DATE_CREATION        = "Date de création";
    public const PREF_CRM_COTATION_DATE_MODIFICATION    = "Dernière modification";
    public const PREF_CRM_COTATION_UTILISATEUR          = "Utilisateur";
    public const PREF_CRM_COTATION_ENTREPRISE           = "Entreprise";
    public const TAB_CRM_COTATIONS = [
        self::PREF_CRM_COTATION_ID                                              => 0,
        self::PREF_CRM_COTATION_NOM                                             => 1,
        self::PREF_CRM_COTATION_ASSUREUR                                        => 2,
        self::PREF_CRM_COTATION_MONNAIE                                         => 3,
        self::PREF_CRM_COTATION_PRIME_TOTALE                                    => 4,
        self::PREF_CRM_COTATION_RISQUE                                          => 5,
        self::PREF_CRM_COTATION_PISTE                                           => 6,
        self::PREF_CRM_COTATION_PIECES                                          => 7,
        self::PREF_CRM_COTATION_UTILISATEUR                                     => 8,
        self::PREF_CRM_COTATION_ENTREPRISE                                      => 9,
        self::PREF_CRM_COTATION_DATE_CREATION                                   => 10,
        self::PREF_CRM_COTATION_DATE_MODIFICATION                               => 11
    ];
    //CRM - ETAPES
    public const PREF_CRM_ETAPES_ID                 = "Id";
    public const PREF_CRM_ETAPES_NOM                = "Nom";
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
        self::PREF_CRM_ETAPES_DATE_MODIFICATION     => 5
    ];
    //CRM - PISTE
    public const PREF_CRM_PISTE_ID                          = "Id";
    public const PREF_CRM_PISTE_NOM                         = "Nom";
    public const PREF_CRM_PISTE_CONTACT                     = "Contact";
    public const PREF_CRM_PISTE_OBJECTIF                    = "Objectif";
    public const PREF_CRM_PISTE_MONTANT                     = "Montant";
    public const PREF_CRM_PISTE_ETAPE                       = "Etape actuelle";
    public const PREF_CRM_PISTE_DATE_EXPIRATION             = "Echéance";
    public const PREF_CRM_PISTE_ACTIONS                     = "Actions";
    public const PREF_CRM_PISTE_COTATION                    = "Cotation";
    public const PREF_CRM_PISTE_UTILISATEUR                 = "Utilisateur";
    public const PREF_CRM_PISTE_ENTREPRISE                  = "Entreprise";
    public const PREF_CRM_PISTE_DATE_DE_CREATION            = "Date de création";
    public const PREF_CRM_PISTE_DATE_DE_MODIFICATION        = "Dernière modification";
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
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_tab                             => 13,
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
        self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 24,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 25,
        self::PREF_calc_retrocom                                => 26,
        self::PREF_calc_retrocom_payees                         => 27,
        self::PREF_calc_retrocom_payees_tab_factures            => 28,
        self::PREF_calc_retrocom_solde                          => 29,
        self::PREF_calc_taxes_courtier_tab                      => 30,
        self::PREF_calc_taxes_courtier                          => 31,
        self::PREF_calc_taxes_courtier_payees                   => 32,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 33,
        self::PREF_calc_taxes_courtier_solde                    => 34,
        self::PREF_calc_taxes_assureurs_tab                     => 35,
        self::PREF_calc_taxes_assureurs                         => 36,
        self::PREF_calc_taxes_assureurs_payees                  => 37,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 38,
        self::PREF_calc_taxes_assureurs_solde                   => 39
    ];
    //PRODUCTION - ASSUEUR
    public const PREF_PRO_ASSUREUR_ID                       = "Id";
    public const PREF_PRO_ASSUREUR_NOM                      = "Nom";
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
    public const PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION     = "Dernière modification";
    public const TAB_PRO_ASSUREURS = [
        self::PREF_PRO_ASSUREUR_ID                              => 0,
        self::PREF_PRO_ASSUREUR_NOM                             => 1,
        self::PREF_PRO_ASSUREUR_ADRESSE                         => 2,
        self::PREF_PRO_ASSUREUR_TELEPHONE                       => 3,
        self::PREF_PRO_ASSUREUR_EMAIL                           => 4,
        self::PREF_PRO_ASSUREUR_SITE_WEB                        => 5,
        self::PREF_PRO_ASSUREUR_RCCM                            => 6,
        self::PREF_PRO_ASSUREUR_IDNAT                           => 7,
        self::PREF_PRO_ASSUREUR_LICENCE                         => 8,
        self::PREF_PRO_ASSUREUR_NUM_IMPOT                       => 9,
        self::PREF_PRO_ASSUREUR_IS_REASSUREUR                   => 10,
        self::PREF_PRO_ASSUREUR_UTILISATEUR                     => 11,
        self::PREF_PRO_ASSUREUR_ENTREPRISE                      => 12,
        self::PREF_PRO_ASSUREUR_DATE_DE_CREATION                => 13,
        self::PREF_PRO_ASSUREUR_DATE_DE_MODIFICATION            => 14,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_tab                             => 15,
        self::PREF_calc_polices_primes_nette                    => 16,
        self::PREF_calc_polices_primes_totale                   => 17,
        self::PREF_calc_polices_fronting                        => 18,
        self::PREF_calc_polices_accessoire                      => 19,
        self::PREF_calc_polices_tva                             => 20,
        self::PREF_calc_revenu_reserve                          => 21,
        self::PREF_calc_revenu_partageable                      => 22,
        self::PREF_calc_revenu_ht                               => 23,
        self::PREF_calc_revenu_ttc                              => 24,
        self::PREF_calc_revenu_ttc_encaisse                     => 25,
        self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 26,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 27,
        self::PREF_calc_retrocom                                => 28,
        self::PREF_calc_retrocom_payees                         => 29,
        self::PREF_calc_retrocom_payees_tab_factures            => 30,
        self::PREF_calc_retrocom_solde                          => 31,
        self::PREF_calc_taxes_courtier_tab                      => 32,
        self::PREF_calc_taxes_courtier                          => 33,
        self::PREF_calc_taxes_courtier_payees                   => 34,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 35,
        self::PREF_calc_taxes_courtier_solde                    => 36,
        self::PREF_calc_taxes_assureurs_tab                     => 37,
        self::PREF_calc_taxes_assureurs                         => 38,
        self::PREF_calc_taxes_assureurs_payees                  => 39,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 40,
        self::PREF_calc_taxes_assureurs_solde                   => 41
    ];
    //PRODUCTION - ENGIN
    public const PREF_PRO_ENGIN_ID                          = "Id";
    public const PREF_PRO_ENGIN_MODEL                       = "Modèl";
    public const PREF_PRO_ENGIN_MARQUE                      = "Marque";
    public const PREF_PRO_ENGIN_ANNEE                       = "Année";
    public const PREF_PRO_ENGIN_PUISSANCE                   = "Puissance";
    public const PREF_PRO_ENGIN_MONNAIE                     = "Monnaie";
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
        self::PREF_PRO_ENGIN_MONNAIE                => 5,
        self::PREF_PRO_ENGIN_VALEUR                 => 6,
        self::PREF_PRO_ENGIN_NB_SIEGES              => 7,
        self::PREF_PRO_ENGIN_USAGE                  => 8,
        self::PREF_PRO_ENGIN_NATURE                 => 9,
        self::PREF_PRO_ENGIN_N°_PLAQUE              => 10,
        self::PREF_PRO_ENGIN_N°_CHASSIS             => 11,
        self::PREF_PRO_ENGIN_POLICE                 => 12,
        self::PREF_PRO_ENGIN_UTILISATEUR            => 13,
        self::PREF_PRO_ENGIN_ENTREPRISE             => 14,
        self::PREF_PRO_ENGIN_DATE_DE_CREATION       => 15,
        self::PREF_PRO_ENGIN_DATE_DE_MODIFICATION   => 16
    ];
    //PRODUCTION - CONTACT
    public const PREF_PRO_CONTACT_ID                        = "Id";
    public const PREF_PRO_CONTACT_NOM                       = "Nom";
    public const PREF_PRO_CONTACT_POSTE                     = "Poste";
    public const PREF_PRO_CONTACT_TELEPHONE                 = "Téléphone";
    public const PREF_PRO_CONTACT_EMAIL                     = "Email";
    public const PREF_PRO_CONTACT_CLIENT                    = "Client";
    public const PREF_PRO_CONTACT_UTILISATEUR               = "Utilisateur";
    public const PREF_PRO_CONTACT_ENTREPRISE                = "Entreprise";
    public const PREF_PRO_CONTACT_DATE_DE_CREATION          = "Date de création";
    public const PREF_PRO_CONTACT_DATE_DE_MODIFICATION      = "Dernière modification";
    public const TAB_PRO_CONTACTS = [
        self::PREF_PRO_CONTACT_ID                       => 0,
        self::PREF_PRO_CONTACT_NOM                      => 1,
        self::PREF_PRO_CONTACT_POSTE                    => 2,
        self::PREF_PRO_CONTACT_TELEPHONE                => 3,
        self::PREF_PRO_CONTACT_EMAIL                    => 4,
        self::PREF_PRO_CONTACT_CLIENT                   => 5,
        self::PREF_PRO_CONTACT_UTILISATEUR              => 6,
        self::PREF_PRO_CONTACT_ENTREPRISE               => 7,
        self::PREF_PRO_CONTACT_DATE_DE_CREATION         => 8,
        self::PREF_PRO_CONTACT_DATE_DE_MODIFICATION     => 9
    ];
    //PRODUCTION - CLIENT
    public const PREF_PRO_CLIENT_ID                         = "Id";
    public const PREF_PRO_CLIENT_NOM                        = "Nom";
    public const PREF_PRO_CLIENT_PERSONNE_MORALE            = "Société";
    public const PREF_PRO_CLIENT_ADRESSE                    = "Adresse";
    public const PREF_PRO_CLIENT_TELEPHONE                  = "Téléphone";
    public const PREF_PRO_CLIENT_EMAIL                      = "Email";
    public const PREF_PRO_CLIENT_SITEWEB                    = "Site Internet";
    public const PREF_PRO_CLIENT_RCCM                       = "Rccm";
    public const PREF_PRO_CLIENT_IDNAT                      = "Id. Nationale";
    public const PREF_PRO_CLIENT_NUM_IMPOT                  = "N°. Impôt";
    public const PREF_PRO_CLIENT_SECTEUR                    = "Secteur";
    public const PREF_PRO_CLIENT_UTILISATEUR                = "Utilisateur";
    public const PREF_PRO_CLIENT_ENTREPRISE                 = "Entreprise";
    public const PREF_PRO_CLIENT_DATE_DE_CREATION           = "Date de création";
    public const PREF_PRO_CLIENT_DATE_DE_MODIFICATION       = "Dernière modification";
    public const TAB_PRO_CLIENTS = [
        self::PREF_PRO_CLIENT_ID                        => 0,
        self::PREF_PRO_CLIENT_NOM                               => 1,
        self::PREF_PRO_CLIENT_PERSONNE_MORALE                   => 2,
        self::PREF_PRO_CLIENT_ADRESSE                           => 3,
        self::PREF_PRO_CLIENT_TELEPHONE                         => 4,
        self::PREF_PRO_CLIENT_EMAIL                             => 5,
        self::PREF_PRO_CLIENT_SITEWEB                           => 6,
        self::PREF_PRO_CLIENT_RCCM                              => 7,
        self::PREF_PRO_CLIENT_IDNAT                             => 8,
        self::PREF_PRO_CLIENT_NUM_IMPOT                         => 9,
        self::PREF_PRO_CLIENT_SECTEUR                           => 10,
        self::PREF_PRO_CLIENT_UTILISATEUR                       => 11,
        self::PREF_PRO_CLIENT_ENTREPRISE                        => 12,
        self::PREF_PRO_CLIENT_DATE_DE_CREATION                  => 13,
        self::PREF_PRO_CLIENT_DATE_DE_MODIFICATION              => 14,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_tab                             => 15,
        self::PREF_calc_polices_primes_nette                    => 16,
        self::PREF_calc_polices_primes_totale                   => 17,
        self::PREF_calc_polices_fronting                        => 18,
        self::PREF_calc_polices_accessoire                      => 19,
        self::PREF_calc_polices_tva                             => 20,
        self::PREF_calc_revenu_reserve                          => 21,
        self::PREF_calc_revenu_partageable                      => 22,
        self::PREF_calc_revenu_ht                               => 23,
        self::PREF_calc_revenu_ttc                              => 24,
        self::PREF_calc_revenu_ttc_encaisse                     => 25,
        self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 26,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 27,
        self::PREF_calc_retrocom                                => 28,
        self::PREF_calc_retrocom_payees                         => 29,
        self::PREF_calc_retrocom_payees_tab_factures            => 30,
        self::PREF_calc_retrocom_solde                          => 31,
        self::PREF_calc_taxes_courtier_tab                      => 32,
        self::PREF_calc_taxes_courtier                          => 33,
        self::PREF_calc_taxes_courtier_payees                   => 34,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 35,
        self::PREF_calc_taxes_courtier_solde                    => 36,
        self::PREF_calc_taxes_assureurs_tab                     => 37,
        self::PREF_calc_taxes_assureurs                         => 38,
        self::PREF_calc_taxes_assureurs_payees                  => 39,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 40,
        self::PREF_calc_taxes_assureurs_solde                   => 41
    ];
    //PRODUCTION - PARTENAIRE
    public const PREF_PRO_PARTENAIRE_ID                         = "Id";
    public const PREF_PRO_PARTENAIRE_NOM                        = "Nom";
    public const PREF_PRO_PARTENAIRE_PART                       = "Part";
    public const PREF_PRO_PARTENAIRE_ADRESSE                    = "Adresse";
    public const PREF_PRO_PARTENAIRE_EMAIL                      = "Email";
    public const PREF_PRO_PARTENAIRE_SITEWEB                    = "Site Internet";
    public const PREF_PRO_PARTENAIRE_RCCM                       = "Rccm";
    public const PREF_PRO_PARTENAIRE_IDNAT                      = "Id. Nationale";
    public const PREF_PRO_PARTENAIRE_NUM_IMPOT                  = "N° Impôt";
    public const PREF_PRO_PARTENAIRE_UTILISATEUR                = "Utilisateur";
    public const PREF_PRO_PARTENAIRE_ENTREPRISE                 = "Entreprise";
    public const PREF_PRO_PARTENAIRE_DATE_DE_CREATION           = "Date de création";
    public const PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION       = "Dernière modification";
    public const TAB_PRO_PARTENAIRES = [
        self::PREF_PRO_PARTENAIRE_ID                            => 0,
        self::PREF_PRO_PARTENAIRE_NOM                           => 1,
        self::PREF_PRO_PARTENAIRE_PART                          => 2,
        self::PREF_PRO_PARTENAIRE_ADRESSE                       => 3,
        self::PREF_PRO_PARTENAIRE_EMAIL                         => 4,
        self::PREF_PRO_PARTENAIRE_SITEWEB                       => 5,
        self::PREF_PRO_PARTENAIRE_RCCM                          => 6,
        self::PREF_PRO_PARTENAIRE_IDNAT                         => 7,
        self::PREF_PRO_PARTENAIRE_NUM_IMPOT                     => 8,
        self::PREF_PRO_PARTENAIRE_UTILISATEUR                   => 9,
        self::PREF_PRO_PARTENAIRE_ENTREPRISE                    => 10,
        self::PREF_PRO_PARTENAIRE_DATE_DE_CREATION              => 11,
        self::PREF_PRO_PARTENAIRE_DATE_DE_MODIFICATION          => 12,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_tab                             => 13,
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
        self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 24,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 25,
        self::PREF_calc_retrocom                                => 26,
        self::PREF_calc_retrocom_payees                         => 27,
        self::PREF_calc_retrocom_payees_tab_factures            => 28,
        self::PREF_calc_retrocom_solde                          => 29,
        self::PREF_calc_taxes_courtier_tab                      => 30,
        self::PREF_calc_taxes_courtier                          => 31,
        self::PREF_calc_taxes_courtier_payees                   => 32,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 33,
        self::PREF_calc_taxes_courtier_solde                    => 34,
        self::PREF_calc_taxes_assureurs_tab                     => 35,
        self::PREF_calc_taxes_assureurs                         => 36,
        self::PREF_calc_taxes_assureurs_payees                  => 37,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 38,
        self::PREF_calc_taxes_assureurs_solde                   => 39
    ];
    //PRODUCTION - POLICE
    public const PREF_PRO_POLICE_ID                                         = "id";
    public const PREF_PRO_POLICE_REFERENCE                                  = "Référence";
    public const PREF_PRO_POLICE_DATE_OPERATION                             = "Date d'opération";
    public const PREF_PRO_POLICE_DATE_EMISSION                              = "Date d'émission";
    public const PREF_PRO_POLICE_DATE_EFFET                                 = "Date d'effet";
    public const PREF_PRO_POLICE_DATE_EXPIRATION                            = "Date d'expiration";
    public const PREF_PRO_POLICE_ID_AVENANT                                 = "Id de l'avenant";
    public const PREF_PRO_POLICE_TYPE_AVENANT                               = "Type d'avenant";
    public const PREF_PRO_POLICE_CAPITAL                                    = "Capital assuré";
    public const PREF_PRO_POLICE_PRIME_NETTE                                = "Prime nette";
    public const PREF_PRO_POLICE_FRONTING                                   = "Fronting";
    public const PREF_PRO_POLICE_ARCA                                       = "Taxe du Reg.";
    public const PREF_PRO_POLICE_TVA                                        = "Tva";
    public const PREF_PRO_POLICE_FRAIS_ADMIN                                = "Accessoire";
    public const PREF_PRO_POLICE_PRIME_TOTALE                               = "Prime totale";
    public const PREF_PRO_POLICE_DISCOUNT                                   = "Remise";
    public const PREF_PRO_POLICE_MODE_PAIEMENT                              = "Mode de paiement";
    public const PREF_PRO_POLICE_RI_COM                                     = "Com. de réass.";
    public const PREF_PRO_POLICE_LOCAL_COM                                  = "Com. locale";
    public const PREF_PRO_POLICE_FRONTIN_COM                                = "Com. sur Fronting";
    public const PREF_PRO_POLICE_REMARQUE                                   = "Remarques";
    public const PREF_PRO_POLICE_MONNAIE                                    = "Monnaie";
    public const PREF_PRO_POLICE_CLIENT                                     = "Client";
    public const PREF_PRO_POLICE_PRODUIT                                    = "Produit";
    public const PREF_PRO_POLICE_PARTENAIRE                                 = "Partenaire";
    public const PREF_PRO_POLICE_PART_EXCEPTIONNELLE                        = "Part except.";
    public const PREF_PRO_POLICE_REASSUREURS                                = "Réassureurs";
    public const PREF_PRO_POLICE_ASSUREURS                                  = "Assureurs";
    public const PREF_PRO_POLICE_PISTE                                      = "Piste";
    public const PREF_PRO_POLICE_GESTIONNAIRE                               = "Gestionnaire";
    public const PREF_PRO_POLICE_CANHSARE_RI_COM                            = "Partager Com. de réa.?";
    public const PREF_PRO_POLICE_CANHSARE_LOCAL_COM                         = "Partager Com. locale?";
    public const PREF_PRO_POLICE_CANHSARE_FRONTING_COM                      = "Partager Com. Fronting?";
    public const PREF_PRO_POLICE_RI_COM_PAYABLE_BY                          = "Com. de réass. dûe par";
    public const PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY                    = "Com. sur Front. dûe par";
    public const PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY                       = "Com. locale dûe par";
    public const PREF_PRO_POLICE_PIECES                                     = "Documents";
    public const PREF_PRO_POLICE_UTILISATEUR                                = "Utilisateur";
    public const PREF_PRO_POLICE_ENTREPRISE                                 = "Entreprise";
    public const PREF_PRO_POLICE_DATE_DE_CREATION                           = "Date de création";
    public const PREF_PRO_POLICE_DATE_DE_MODIFICATION                       = "Dernière modification";

    public const TAB_PRO_POLICES = [
        self::PREF_PRO_POLICE_ID                                => 0,
        self::PREF_PRO_POLICE_REFERENCE                         => 1,
        self::PREF_PRO_POLICE_DATE_OPERATION                    => 2,
        self::PREF_PRO_POLICE_DATE_EMISSION                     => 3,
        self::PREF_PRO_POLICE_DATE_EFFET                        => 4,
        self::PREF_PRO_POLICE_DATE_EXPIRATION                   => 5,
        self::PREF_PRO_POLICE_ID_AVENANT                        => 6,
        self::PREF_PRO_POLICE_TYPE_AVENANT                      => 7,
        self::PREF_PRO_POLICE_CAPITAL                           => 8,
        self::PREF_PRO_POLICE_PRIME_NETTE                       => 9,
        self::PREF_PRO_POLICE_FRONTING                          => 10,
        self::PREF_PRO_POLICE_ARCA                              => 11,
        self::PREF_PRO_POLICE_TVA                               => 12,
        self::PREF_PRO_POLICE_FRAIS_ADMIN                       => 13,
        self::PREF_PRO_POLICE_PRIME_TOTALE                      => 14,
        self::PREF_PRO_POLICE_DISCOUNT                          => 15,
        self::PREF_PRO_POLICE_MODE_PAIEMENT                     => 16,
        self::PREF_PRO_POLICE_RI_COM                            => 17,
        self::PREF_PRO_POLICE_LOCAL_COM                         => 18,
        self::PREF_PRO_POLICE_FRONTIN_COM                       => 19,
        self::PREF_PRO_POLICE_REMARQUE                          => 20,
        self::PREF_PRO_POLICE_MONNAIE                           => 21,
        self::PREF_PRO_POLICE_CLIENT                            => 22,
        self::PREF_PRO_POLICE_PRODUIT                           => 23,
        self::PREF_PRO_POLICE_PARTENAIRE                        => 24,
        self::PREF_PRO_POLICE_PART_EXCEPTIONNELLE               => 25,
        self::PREF_PRO_POLICE_REASSUREURS                       => 26,
        self::PREF_PRO_POLICE_ASSUREURS                         => 27,
        self::PREF_PRO_POLICE_PISTE                             => 28,
        self::PREF_PRO_POLICE_GESTIONNAIRE                      => 29,
        self::PREF_PRO_POLICE_CANHSARE_RI_COM                   => 30,
        self::PREF_PRO_POLICE_CANHSARE_LOCAL_COM                => 31,
        self::PREF_PRO_POLICE_CANHSARE_FRONTING_COM             => 32,
        self::PREF_PRO_POLICE_RI_COM_PAYABLE_BY                 => 33,
        self::PREF_PRO_POLICE_FRONTING_COM_PAYABLE_BY           => 34,
        self::PREF_PRO_POLICE_LOCAL_COM_PAYABLE_BY              => 35,
        self::PREF_PRO_POLICE_PIECES                            => 36,
        self::PREF_PRO_POLICE_UTILISATEUR                       => 37,
        self::PREF_PRO_POLICE_ENTREPRISE                        => 38,
        self::PREF_PRO_POLICE_DATE_DE_CREATION                  => 39,
        self::PREF_PRO_POLICE_DATE_DE_MODIFICATION              => 40,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        //self::PREF_calc_polices_tab                             => 41,
        //self::PREF_calc_polices_primes_nette                    => 42,
        //self::PREF_calc_polices_primes_totale                   => 43,
        //self::PREF_calc_polices_fronting                        => 44,
        //self::PREF_calc_polices_accessoire                      => 45,
        //self::PREF_calc_polices_tva                             => 46,
        self::PREF_calc_revenu_reserve                          => 41,
        self::PREF_calc_revenu_partageable                      => 42,
        self::PREF_calc_revenu_ht                               => 43,
        self::PREF_calc_revenu_ttc                              => 44,
        self::PREF_calc_revenu_ttc_encaisse                     => 45,
        self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 46,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 47,
        self::PREF_calc_retrocom                                => 48,
        self::PREF_calc_retrocom_payees                         => 49,
        self::PREF_calc_retrocom_payees_tab_factures            => 50,
        self::PREF_calc_retrocom_solde                          => 51,
        self::PREF_calc_taxes_courtier_tab                      => 52,
        self::PREF_calc_taxes_courtier                          => 53,
        self::PREF_calc_taxes_courtier_payees                   => 54,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 55,
        self::PREF_calc_taxes_courtier_solde                    => 56,
        self::PREF_calc_taxes_assureurs_tab                     => 57,
        self::PREF_calc_taxes_assureurs                         => 58,
        self::PREF_calc_taxes_assureurs_payees                  => 59,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 60,
        self::PREF_calc_taxes_assureurs_solde                   => 61
    ];
    //PRODUCTION - PRODUIT
    public const PREF_PRO_PRODUIT_ID                        = "Id";
    public const PREF_PRO_PRODUIT_CODE                      = "Code";
    public const PREF_PRO_PRODUIT_NOM                       = "Nom";
    public const PREF_PRO_PRODUIT_DESCRIPTION               = "Description";
    public const PREF_PRO_PRODUIT_TAUX_COMMISSION           = "Taux/Comm.";
    public const PREF_PRO_PRODUIT_OBJIGATOIRE               = "Obligatoire";
    public const PREF_PRO_PRODUIT_ABONNEMENT                = "Abonnement";
    public const PREF_PRO_PRODUIT_CATEGORIE                 = "Catégorie";
    public const PREF_PRO_PRODUIT_UTILISATEUR               = "Utilisateur";
    public const PREF_PRO_PRODUIT_ENTREPRISE                = "Entreprise";
    public const PREF_PRO_PRODUIT_DATE_DE_CREATION          = "Date de création";
    public const PREF_PRO_PRODUIT_DATE_DE_MODIFICATION      = "Dernière modification";
    public const TAB_PRO_PRODUITS = [
        self::PREF_PRO_PRODUIT_ID                               => 0,
        self::PREF_PRO_PRODUIT_CODE                             => 1,
        self::PREF_PRO_PRODUIT_NOM                              => 2,
        self::PREF_PRO_PRODUIT_DESCRIPTION                      => 3,
        self::PREF_PRO_PRODUIT_TAUX_COMMISSION                  => 4,
        self::PREF_PRO_PRODUIT_OBJIGATOIRE                      => 5,
        self::PREF_PRO_PRODUIT_ABONNEMENT                       => 6,
        self::PREF_PRO_PRODUIT_CATEGORIE                        => 7,
        self::PREF_PRO_PRODUIT_UTILISATEUR                      => 8,
        self::PREF_PRO_PRODUIT_ENTREPRISE                       => 9,
        self::PREF_PRO_PRODUIT_DATE_DE_CREATION                 => 10,
        self::PREF_PRO_PRODUIT_DATE_DE_MODIFICATION             => 11,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_tab                             => 12,
        self::PREF_calc_polices_primes_nette                    => 13,
        self::PREF_calc_polices_primes_totale                   => 14,
        self::PREF_calc_polices_fronting                        => 15,
        self::PREF_calc_polices_accessoire                      => 16,
        self::PREF_calc_polices_tva                             => 17,
        self::PREF_calc_revenu_reserve                          => 18,
        self::PREF_calc_revenu_partageable                      => 19,
        self::PREF_calc_revenu_ht                               => 20,
        self::PREF_calc_revenu_ttc                              => 21,
        self::PREF_calc_revenu_ttc_encaisse                     => 22,
        self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 23,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 24,
        self::PREF_calc_retrocom                                => 25,
        self::PREF_calc_retrocom_payees                         => 26,
        self::PREF_calc_retrocom_payees_tab_factures            => 27,
        self::PREF_calc_retrocom_solde                          => 28,
        self::PREF_calc_taxes_courtier_tab                      => 29,
        self::PREF_calc_taxes_courtier                          => 30,
        self::PREF_calc_taxes_courtier_payees                   => 31,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 32,
        self::PREF_calc_taxes_courtier_solde                    => 33,
        self::PREF_calc_taxes_assureurs_tab                     => 34,
        self::PREF_calc_taxes_assureurs                         => 35,
        self::PREF_calc_taxes_assureurs_payees                  => 36,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 37,
        self::PREF_calc_taxes_assureurs_solde                   => 38
    ];

    //FINANCE - TAXES
    public const PREF_FIN_TAXE_ID                       = "Id";
    public const PREF_FIN_TAXE_NOM                      = "Nom";
    public const PREF_FIN_TAXE_DESCRIPTION              = "Description";
    public const PREF_FIN_TAXE_TAUX                     = "Taux";
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
        self::PREF_FIN_TAXE_TAUX                                => 3,
        self::PREF_FIN_TAXE_ORGANISATION                        => 5,
        self::PREF_FIN_TAXE_PAR_COURTIER                        => 6,
        self::PREF_FIN_TAXE_UTILISATEUR                         => 7,
        self::PREF_FIN_TAXE_ENTREPRISE                          => 8,
        self::PREF_FIN_TAXE_DATE_DE_CREATION                    => 9,
        self::PREF_FIN_TAXE_DERNIERE_MODIFICATION               => 10,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        //self::PREF_calc_polices_tab                             => 11,
        //self::PREF_calc_polices_primes_nette                    => 12,
        //self::PREF_calc_polices_primes_totale                   => 13,
        //self::PREF_calc_polices_fronting                        => 14,
        //self::PREF_calc_polices_accessoire                      => 15,
        //self::PREF_calc_polices_tva                             => 16,
        //self::PREF_calc_revenu_reserve                          => 17,
        //self::PREF_calc_revenu_partageable                      => 18,
        //self::PREF_calc_revenu_ht                               => 19,
        //self::PREF_calc_revenu_ttc                              => 20,
        //self::PREF_calc_revenu_ttc_encaisse                     => 21,
        //self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 22,
        //self::PREF_calc_revenu_ttc_solde_restant_du             => 23,
        //self::PREF_calc_retrocom                                => 24,
        //self::PREF_calc_retrocom_payees                         => 25,
        //self::PREF_calc_retrocom_payees_tab_factures            => 26,
        //self::PREF_calc_retrocom_solde                          => 27,
        self::PREF_calc_taxes_courtier_tab                      => 11,
        self::PREF_calc_taxes_courtier                          => 12,
        self::PREF_calc_taxes_courtier_payees                   => 13,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 14,
        self::PREF_calc_taxes_courtier_solde                    => 15,
        self::PREF_calc_taxes_assureurs_tab                     => 16,
        self::PREF_calc_taxes_assureurs                         => 17,
        self::PREF_calc_taxes_assureurs_payees                  => 18,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 19,
        self::PREF_calc_taxes_assureurs_solde                   => 20
    ];

    //FINANCE - MONNAIE
    public const PREF_FIN_MONNAIE_ID                    = "Id";
    public const PREF_FIN_MONNAIE_NOM                   = "Nom";
    public const PREF_FIN_MONNAIE_CODE                  = "Code";
    public const PREF_FIN_MONNAIE_TAUX_USD              = "Taux (en USD)";
    public const PREF_FIN_MONNAIE_IS_LOCALE             = "Monnaie Locale?";
    public const PREF_FIN_MONNAIE_UTILISATEUR           = "Utilisateur";
    public const PREF_FIN_MONNAIE_ENTREPRISE            = "Entreprise";
    public const PREF_FIN_MONNAIE_DATE_DE_CREATION      = "Date de création";
    public const PREF_FIN_MONNAIE_DERNIRE_MODIFICATION  = "Dernière modification";
    public const TAB_FIN_MONNAIES = [
        self::PREF_FIN_MONNAIE_ID                               => 0,
        self::PREF_FIN_MONNAIE_NOM                              => 1,
        self::PREF_FIN_MONNAIE_CODE                             => 2,
        self::PREF_FIN_MONNAIE_TAUX_USD                         => 3,
        self::PREF_FIN_MONNAIE_IS_LOCALE                        => 4,
        self::PREF_FIN_MONNAIE_UTILISATEUR                      => 5,
        self::PREF_FIN_MONNAIE_ENTREPRISE                       => 6,
        self::PREF_FIN_MONNAIE_DATE_DE_CREATION                 => 7,
        self::PREF_FIN_MONNAIE_DERNIRE_MODIFICATION             => 8
    ];
    //FINANCE - PAIEMENTS COMMISSIONS
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_ID                      = "Id";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_DATE                    = "Date";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_POLICE                  = "Police";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_MONNAIE                 = "Monnaie";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_MONTANT                 = "Montant";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_REF_FACTURE             = "Note de débit";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_DESCRIPTION             = "Description";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_DOCUMENTS               = "Documents";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_UTILISATEUR             = "Utilisateur";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_ENTREPRISE              = "Entreprise";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_DATE_DE_CREATION        = "Date de création";
    public const PREF_FIN_PAIEMENTS_COMMISSIONS_DERNIRE_MODIFICATION    = "Dernière modification";
    public const TAB_FIN_PAIEMENTS_COMMISSIONS = [
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_ID                         => 0,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_DATE                       => 1,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_POLICE                     => 2,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_MONNAIE                    => 3,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_MONTANT                    => 4,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_REF_FACTURE                => 5,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_DESCRIPTION                => 6,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_DOCUMENTS                  => 7,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_UTILISATEUR                => 8,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_ENTREPRISE                 => 9,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_DATE_DE_CREATION           => 10,
        self::PREF_FIN_PAIEMENTS_COMMISSIONS_DERNIRE_MODIFICATION       => 11
    ];
    //FINANCE - PAIEMENTS RETROCOMMISSIONS
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_ID                      = "Id";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DATE                    = "Date";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_POLICE                  = "Police";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_MONNAIE                 = "Monnaie";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_MONTANT                 = "Montant";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_REF_FACTURE             = "Note de débit";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_PARTENAIRE              = "Partenaire";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DOCUMENTS               = "Documents";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_UTILISATEUR             = "Utilisateur";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_ENTREPRISE              = "Entreprise";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DATE_DE_CREATION        = "Date de création";
    public const PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DERNIRE_MODIFICATION    = "Dernière modification";

    public const TAB_FIN_PAIEMENTS_RETROCOMMISSIONS = [
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_ID                         => 0,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DATE                       => 1,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_POLICE                     => 2,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_MONNAIE                    => 3,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_MONTANT                    => 4,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_REF_FACTURE                => 5,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_PARTENAIRE                 => 6,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DOCUMENTS                  => 7,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_UTILISATEUR                => 8,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_ENTREPRISE                 => 9,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DATE_DE_CREATION           => 10,
        self::PREF_FIN_PAIEMENTS_RETROCOMMISSIONS_DERNIRE_MODIFICATION       => 11
    ];
    //FINANCE - PAIEMENTS TAXES
    public const PREF_FIN_PAIEMENTS_TAXE_ID                     = "Id";
    public const PREF_FIN_PAIEMENTS_TAXE_DATE                   = "Date";
    public const PREF_FIN_PAIEMENTS_TAXE_TAXE                   = "Taxe";
    public const PREF_FIN_PAIEMENTS_TAXE_POLICE                 = "Police d'assurance";
    public const PREF_FIN_PAIEMENTS_TAXE_MONNAIE                = "Monnaie";
    public const PREF_FIN_PAIEMENTS_TAXE_MONTANT                = "Montant";
    public const PREF_FIN_PAIEMENTS_TAXE_NOTE_DE_DEBIT          = "Réf. Note de débit";
    public const PREF_FIN_PAIEMENTS_TAXE_EXERCICE               = "Exercice comptable";
    public const PREF_FIN_PAIEMENTS_TAXE_DOCUMENTS              = "Documents";
    public const PREF_FIN_PAIEMENTS_TAXE_UTILISATEUR            = "Utilisateur";
    public const PREF_FIN_PAIEMENTS_TAXE_ENTREPRISE             = "Entreprise";
    public const PREF_FIN_PAIEMENTS_TAXE_DATE_DE_CREATION       = "Date de création";
    public const PREF_FIN_PAIEMENTS_TAXE_DERNIRE_MODIFICATION   = "Dernière modification";
    public const TAB_FIN_PAIEMENTS_TAXES = [
        self::PREF_FIN_PAIEMENTS_TAXE_ID                    => 0,
        self::PREF_FIN_PAIEMENTS_TAXE_DATE                  => 1,
        self::PREF_FIN_PAIEMENTS_TAXE_TAXE                  => 2,
        self::PREF_FIN_PAIEMENTS_TAXE_POLICE                => 3,
        self::PREF_FIN_PAIEMENTS_TAXE_MONNAIE               => 4,
        self::PREF_FIN_PAIEMENTS_TAXE_MONTANT               => 5,
        self::PREF_FIN_PAIEMENTS_TAXE_NOTE_DE_DEBIT         => 6,
        self::PREF_FIN_PAIEMENTS_TAXE_EXERCICE              => 7,
        self::PREF_FIN_PAIEMENTS_TAXE_DOCUMENTS             => 8,
        self::PREF_FIN_PAIEMENTS_TAXE_UTILISATEUR           => 9,
        self::PREF_FIN_PAIEMENTS_TAXE_ENTREPRISE            => 10,
        self::PREF_FIN_PAIEMENTS_TAXE_DATE_DE_CREATION      => 11,
        self::PREF_FIN_PAIEMENTS_TAXE_DERNIRE_MODIFICATION  => 12
    ];

    //SINISTRE - COMMENTAIRE
    public const PREF_SIN_COMMENTAIRE_ID                    = "Id";
    public const PREF_SIN_COMMENTAIRE_MESSAGE               = "Message";
    public const PREF_SIN_COMMENTAIRE_PRECEDENT             = "Précédent Mesg.";
    public const PREF_SIN_COMMENTAIRE_SINISTRE              = "Sinistre";
    public const PREF_SIN_COMMENTAIRE_UTILISATEUR           = "Utilisateur";
    public const PREF_SIN_COMMENTAIRE_ENTREPRISE            = "Entreprise";
    public const PREF_SIN_COMMENTAIRE_DATE_DE_CREATION      = "Date de création";
    public const PREF_SIN_COMMENTAIRE_DERNIRE_MODIFICATION  = "Dernière modification";
    public const TAB_SIN_COMMENTAIRES = [
        self::PREF_SIN_COMMENTAIRE_ID                   => 0,
        self::PREF_SIN_COMMENTAIRE_MESSAGE              => 1,
        self::PREF_SIN_COMMENTAIRE_PRECEDENT            => 2,
        self::PREF_SIN_COMMENTAIRE_SINISTRE             => 3,
        self::PREF_SIN_COMMENTAIRE_UTILISATEUR          => 4,
        self::PREF_SIN_COMMENTAIRE_ENTREPRISE           => 5,
        self::PREF_SIN_COMMENTAIRE_DATE_DE_CREATION     => 6,
        self::PREF_SIN_COMMENTAIRE_DERNIRE_MODIFICATION => 7
    ];
    //SINISTRE - ETAPE
    public const PREF_SIN_ETAPE_ID                      = "Id";
    public const PREF_SIN_ETAPE_NOM                     = "Nom";
    public const PREF_SIN_ETAPE_DESCRIPTION             = "Description";
    public const PREF_SIN_ETAPE_INDICE                  = "Indice";
    public const PREF_SIN_ETAPE_UTILISATEUR             = "Utilisateur";
    public const PREF_SIN_ETAPE_ENTREPRISE              = "Entreprise";
    public const PREF_SIN_ETAPE_DATE_DE_CREATION        = "Date de création";
    public const PREF_SIN_ETAPE_DERNIRE_MODIFICATION    = "Dernière modification";
    public const TAB_SIN_ETAPES = [
        self::PREF_SIN_ETAPE_ID                     => 0,
        self::PREF_SIN_ETAPE_NOM                    => 1,
        self::PREF_SIN_ETAPE_DESCRIPTION            => 2,
        self::PREF_SIN_ETAPE_INDICE                 => 3,
        self::PREF_SIN_ETAPE_UTILISATEUR            => 4,
        self::PREF_SIN_ETAPE_ENTREPRISE             => 5,
        self::PREF_SIN_ETAPE_DATE_DE_CREATION       => 6,
        self::PREF_SIN_ETAPE_DERNIRE_MODIFICATION   => 7
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
    public const PREF_SIN_SINISTRE_ITITRE               = "Titre";
    public const PREF_SIN_SINISTRE_DATE_OCCURENCE       = "Date de l'incident";
    public const PREF_SIN_SINISTRE_DESCRIPTION          = "Description";
    public const PREF_SIN_SINISTRE_VICTIMES             = "Victimes";
    public const PREF_SIN_SINISTRE_EXPERT               = "Expert(s)";
    public const PREF_SIN_SINISTRE_POLICE               = "Police";
    public const PREF_SIN_SINISTRE_COUT                 = "Coût de réparation";
    public const PREF_SIN_SINISTRE_MONTANT_PAYE         = "Compensation";
    public const PREF_SIN_SINISTRE_MONNAIE              = "Monnaie";
    public const PREF_SIN_SINISTRE_DATE_PAIEMENT        = "Date de paiement";
    public const PREF_SIN_SINISTRE_ETAPE                = "Etape actuelle";
    public const PREF_SIN_SINISTRE_DOCUMENTS            = "Documents";
    public const PREF_SIN_SINISTRE_COMMENTAIRE          = "Commentaires";
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
        self::PREF_SIN_SINISTRE_EXPERT                          => 6,
        self::PREF_SIN_SINISTRE_POLICE                          => 7,
        self::PREF_SIN_SINISTRE_COUT                            => 8,
        self::PREF_SIN_SINISTRE_MONTANT_PAYE                    => 9,
        self::PREF_SIN_SINISTRE_MONNAIE                         => 10,
        self::PREF_SIN_SINISTRE_DATE_PAIEMENT                   => 11,
        self::PREF_SIN_SINISTRE_ETAPE                           => 12,
        self::PREF_SIN_SINISTRE_DOCUMENTS                       => 13,
        self::PREF_SIN_SINISTRE_COMMENTAIRE                     => 14,
        self::PREF_SIN_SINISTRE_UTILISATEUR                     => 15,
        self::PREF_SIN_SINISTRE_ENTREPRISE                      => 16,
        self::PREF_SIN_SINISTRE_DATE_DE_CREATION                => 17,
        self::PREF_SIN_SINISTRE_DERNIRE_MODIFICATION            => 18,
        //CHAMPS CALCULABLES AUTOMATIQUEMENT
        self::PREF_calc_polices_tab                             => 19,
        self::PREF_calc_polices_primes_nette                    => 20,
        self::PREF_calc_polices_primes_totale                   => 21,
        self::PREF_calc_polices_fronting                        => 22,
        self::PREF_calc_polices_accessoire                      => 23,
        self::PREF_calc_polices_tva                             => 24,
        self::PREF_calc_revenu_reserve                          => 25,
        self::PREF_calc_revenu_partageable                      => 26,
        self::PREF_calc_revenu_ht                               => 27,
        self::PREF_calc_revenu_ttc                              => 28,
        self::PREF_calc_revenu_ttc_encaisse                     => 29,
        self::PREF_calc_revenu_ttc_encaisse_tab_ref_factures    => 30,
        self::PREF_calc_revenu_ttc_solde_restant_du             => 31,
        self::PREF_calc_retrocom                                => 32,
        self::PREF_calc_retrocom_payees                         => 33,
        self::PREF_calc_retrocom_payees_tab_factures            => 34,
        self::PREF_calc_retrocom_solde                          => 35,
        self::PREF_calc_taxes_courtier_tab                      => 36,
        self::PREF_calc_taxes_courtier                          => 37,
        self::PREF_calc_taxes_courtier_payees                   => 38,
        self::PREF_calc_taxes_courtier_payees_tab_ref_factures  => 39,
        self::PREF_calc_taxes_courtier_solde                    => 40,
        self::PREF_calc_taxes_assureurs_tab                     => 41,
        self::PREF_calc_taxes_assureurs                         => 42,
        self::PREF_calc_taxes_assureurs_payees                  => 43,
        self::PREF_calc_taxes_assureurs_payees_tab_ref_factures => 44,
        self::PREF_calc_taxes_assureurs_solde                   => 45
    ];

    //SINISTRE - VICTIME
    public const PREF_SIN_VICTIME_ID                    = "Id";
    public const PREF_SIN_VICTIME_NOM                   = "Nom";
    public const PREF_SIN_VICTIME_ADRESSE               = "Adresse";
    public const PREF_SIN_VICTIME_TELEPHONE             = "Téléphone";
    public const PREF_SIN_VICTIME_EMAIL                 = "Email";
    public const PREF_SIN_VICTIME_SINISTRE              = "Sinistres";
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
        self::PREF_SIN_VICTIME_DERNIRE_MODIFICATION => 10
    ];
    //BIBLIOTHEQUE - CATEGORIE
    public const PREF_BIB_CATEGORIE_ID                      = "Id";
    public const PREF_BIB_CATEGORIE_NOM                     = "Nom";
    public const PREF_BIB_CATEGORIE_UTILISATEUR             = "Utilisateur";
    public const PREF_BIB_CATEGORIE_ENTREPRISE              = "Entreprise";
    public const PREF_BIB_CATEGORIE_DATE_DE_CREATION        = "Date de création";
    public const PREF_BIB_CATEGORIE_DERNIRE_MODIFICATION    = "Dernière modification";
    public const TAB_BIB_CATEGORIES = [
        self::PREF_BIB_CATEGORIE_ID                     => 0,
        self::PREF_BIB_CATEGORIE_NOM                    => 1,
        self::PREF_BIB_CATEGORIE_UTILISATEUR            => 2,
        self::PREF_BIB_CATEGORIE_ENTREPRISE             => 3,
        self::PREF_BIB_CATEGORIE_DATE_DE_CREATION       => 4,
        self::PREF_BIB_CATEGORIE_DERNIRE_MODIFICATION   => 5
    ];
    //BIBLIOTHEQUE - CLASSEUR
    public const PREF_BIB_CLASSEUR_ID                   = "Id";
    public const PREF_BIB_CLASSEUR_NOM                  = "Nom";
    public const PREF_BIB_CLASSEUR_UTILISATEUR          = "Utilisateur";
    public const PREF_BIB_CLASSEUR_ENTREPRISE           = "Entreprise";
    public const PREF_BIB_CLASSEUR_DATE_DE_CREATION     = "Date de création";
    public const PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION = "Dernière modification";
    public const TAB_BIB_CLASSEURS = [
        self::PREF_BIB_CLASSEUR_ID                      => 0,
        self::PREF_BIB_CLASSEUR_NOM                     => 1,
        self::PREF_BIB_CLASSEUR_UTILISATEUR             => 2,
        self::PREF_BIB_CLASSEUR_ENTREPRISE              => 3,
        self::PREF_BIB_CLASSEUR_DATE_DE_CREATION        => 4,
        self::PREF_BIB_CLASSEUR_DERNIRE_MODIFICATION    => 5
    ];
    //BIBLIOTHEQUE - DOCUMENT
    public const PREF_BIB_DOCUMENT_ID                   = "Id";
    public const PREF_BIB_DOCUMENT_NOM                  = "Intitulé du document";
    public const PREF_BIB_DOCUMENT_CATEGORIE            = "Catégorie";
    public const PREF_BIB_DOCUMENT_CLASSEUR             = "Classeur";
    public const PREF_BIB_DOCUMENT_DESCRIPTION          = "Description";
    public const PREF_BIB_DOCUMENT_UTILISATEUR          = "Utilisateur";
    public const PREF_BIB_DOCUMENT_ENTREPRISE           = "Entreprise";
    public const PREF_BIB_DOCUMENT_DATE_DE_CREATION     = "Date de création";
    public const PREF_BIB_DOCUMENT_DERNIRE_MODIFICATION = "Dernière modification";
    public const TAB_BIB_DOCUMENTS = [
        self::PREF_BIB_DOCUMENT_ID                      => 0,
        self::PREF_BIB_DOCUMENT_NOM                     => 1,
        self::PREF_BIB_DOCUMENT_CATEGORIE               => 2,
        self::PREF_BIB_DOCUMENT_CLASSEUR                => 3,
        self::PREF_BIB_DOCUMENT_DESCRIPTION             => 4,
        self::PREF_BIB_DOCUMENT_UTILISATEUR             => 5,
        self::PREF_BIB_DOCUMENT_ENTREPRISE              => 6,
        self::PREF_BIB_DOCUMENT_DATE_DE_CREATION        => 7,
        self::PREF_BIB_DOCUMENT_DERNIRE_MODIFICATION    => 8
    ];
    //PARAMETRES - UTILISATEUR
    public const PREF_PAR_UTILISATEUR_ID                    = "Id";
    public const PREF_PAR_UTILISATEUR_NOM                   = "Nom";
    public const PREF_PAR_UTILISATEUR_PSEUDO                = "Speudo";
    public const PREF_PAR_UTILISATEUR_EMAIL                 = "Email";
    public const PREF_PAR_UTILISATEUR_ROLES                 = "Rôles";
    public const PREF_PAR_UTILISATEUR_UTILISATEUR           = "Utilisateur";
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
    ];


    public const PREF_APPARENCE_CLAIRE = 'Mode sombre désactivé';
    public const PREF_APPARENCE_SOMBRE = 'Mode sombre activé';
    public const TAB_APPARENCES = [
        self::PREF_APPARENCE_CLAIRE     => 0,
        self::PREF_APPARENCE_SOMBRE     => 1
    ];
    public const PREF_UTILISATEUR = "Utilisateur";
    public const PREF_ENTREPRISE = "Entreprise";


    public function __construct(
        private EntityManagerInterface $entityManager,
        private ServiceEntreprise $serviceEntreprise
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
            NumberField::new('crmTaille', "Eléments par page")->setColumns(2), //->setColumns(3),
            ChoiceField::new('crmPistes', "Attributs Piste")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_PISTE),
            ChoiceField::new('crmMissions', "Attributs Mission")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_MISSIONS),
            ChoiceField::new('crmFeedbacks', "Attributs Feedback")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_FEEDBACKS),
            ChoiceField::new('crmCotations', "Attributs Cotations")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_COTATIONS),
            ChoiceField::new('crmEtapes', "Attributs Etapes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_CRM_ETAPES),

            //Onglet 03 - PRODUCTION
            FormField::addTab(' PRODUCTION')
                ->setIcon('fas fa-bag-shopping')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section PRODUCTION."),
            NumberField::new('proTaille', "Eléments par page")->setColumns(2),
            ChoiceField::new('proAssureurs', "Attributs Assureur")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_ASSUREURS),
            ChoiceField::new('proPolices', "Attributs Polices")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_POLICES),
            ChoiceField::new('proProduits', "Attributs Produits")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_PRODUITS),
            ChoiceField::new('proClients', "Attributs Clients")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_CLIENTS),
            ChoiceField::new('proPartenaires', "Attributs Partenaires")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_PARTENAIRES),
            ChoiceField::new('proAutomobiles', "Attributs Engins")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_ENGINS),
            ChoiceField::new('proContacts', "Attributs Contact")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_PRO_CONTACTS),

            //Onglet 04 - FINANCES
            FormField::addTab(' FINANCES')
                ->setIcon('fas fa-sack-dollar')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section FINANCES."),
            NumberField::new('finTaille', "Eléments par page")->setColumns(2),
            ChoiceField::new('finTaxes', "Attributs Taxes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_TAXES),
            ChoiceField::new('finMonnaies', "Attributs Monnaies")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_MONNAIES),
            ChoiceField::new('finCommissionsPayees', "Attributs Com. encaissées")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_PAIEMENTS_COMMISSIONS),
            ChoiceField::new('finRetrocommissionsPayees', "Attributs RetroCom. payées")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_PAIEMENTS_RETROCOMMISSIONS),
            ChoiceField::new('finTaxesPayees', "Attributs Taxes payées")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_FIN_PAIEMENTS_TAXES),

            //Onglet 05 - SINISTRE
            FormField::addTab(' SINISTRE')
                ->setIcon('fas fa-fire')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section SINISTRE."),
            NumberField::new('sinTaille', "Eléments par page")->setColumns(2),
            ChoiceField::new('sinSinistres', "Attributs Sinistres")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_SINISTRES),
            ChoiceField::new('sinCommentaires', "Attributs Commentaires")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_COMMENTAIRES),
            ChoiceField::new('sinEtapes', "Attributs Etapes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_ETAPES),
            ChoiceField::new('sinExperts', "Attributs Experts")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_EXPERTS),
            ChoiceField::new('sinVictimes', "Attributs Victimes")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_SIN_VICTIMES),


            //Onglet 06 - BIBLIOTHEQUE
            FormField::addTab(' BIBLIOTHEQUE')
                ->setIcon('fas fa-book')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section BIBLIOTHEQUE."),
            NumberField::new('bibTaille', "Eléments par page")->setColumns(2),
            ChoiceField::new('bibCategories', "Attributs Catégories")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_BIB_CATEGORIES),
            ChoiceField::new('bibClasseurs', "Attributs Classeurs")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_BIB_CLASSEURS),
            ChoiceField::new('bibPieces', "Attributs Documents")
                ->setColumns(2)
                ->renderExpanded()
                ->allowMultipleChoices()
                ->setChoices(self::TAB_BIB_DOCUMENTS),

            //Onglet 07 - PARAMETRES
            FormField::addTab(' PARAMETRES')
                ->setIcon('fas fa-gears')
                ->setHelp("Les paramètres qui s'appliquent uniquement sur les fonctions de la section PARAMETRES."),
            NumberField::new('parTaille', "Eléments par page")->setColumns(2),
            ChoiceField::new('parUtilisateurs', "Attributs Utilisateurs")
            ->setColumns(2)
            ->renderExpanded()
            ->allowMultipleChoices()
            ->setChoices(self::TAB_PAR_UTILISATEURS),

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            //les Updates sur la page détail
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })
            //Updates Sur la page Edit
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setIcon('fa-solid fa-floppy-disk')->setLabel(DashboardController::ACTION_ENREGISTRER); //<i class="fa-solid fa-floppy-disk"></i>
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa-solid fa-pen-to-square')->setLabel(DashboardController::ACTION_MODIFIER);
            })

            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::INDEX)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }
}
