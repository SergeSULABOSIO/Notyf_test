<?php

namespace App\Service;

use App\Entity\Taxe;
use App\Entity\Piste;
use App\Entity\Client;
use App\Entity\Expert;
use App\Entity\Police;
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
use App\Entity\Partenaire;
use App\Entity\DocClasseur;
use App\Entity\FeedbackCRM;
use App\Entity\DocCategorie;
use App\Entity\EtapeSinistre;
use App\Entity\Facture;
use App\Entity\Paiement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Service\ServiceEntreprise as ServiceServiceEntreprise;

class ServiceSuppression
{
    public const FINANCE_TAXE = 0;
    public const FINANCE_MONNAIE = 1;
    public const FINANCE_PAIEMENT_TAXE = 2;
    public const FINANCE_PAIEMENT_COMMISSION = 3;
    public const FINANCE_PAIEMENT_PARTENAIRE = 4;

    public const PRODUCTION_CONTACT = 5;
    public const PRODUCTION_ENGIN = 6;
    public const PRODUCTION_POLICE = 7;
    public const PRODUCTION_ASSUREUR = 8;
    public const PRODUCTION_PRODUIT = 9;
    public const PRODUCTION_CLIENT = 10;
    public const PRODUCTION_PARTENAIRE = 11;

    public const SINISTRE_COMMENTAIRE = 12;
    public const SINISTRE_ETAPE = 13;
    public const SINISTRE_VICTIME = 14;
    public const SINISTRE_EXPERT = 15;
    public const SINISTRE_SINISTRE = 16;

    public const CRM_COTATION = 17;
    public const CRM_ACTION = 18;
    public const CRM_FEEDBACK = 19;
    public const CRM_ETAPE = 20;
    public const CRM_PISTE = 21;

    public const BIBLIOTHEQUE_PIECE = 22;
    public const BIBLIOTHEQUE_CLASSE = 23;
    public const BIBLIOTHEQUE_CATEGORIE = 24;

    public const PAREMETRE_UTILISATEUR = 25;
    public const PAREMETRE_ENTREPRISE = 26;

    public const FINANCE_FACTURE = 27;
    public const FINANCE_ELEMENT_FACTURE = 28;
    public const FINANCE_COMPTE_BANCAIRE = 29;
    public const FINANCE_PAIEMENT = 30;
    public const FINANCE_REVENU = 31;
    public const PRODUCTION_CHARGEMENT = 32;
    public const PRODUCTION_TRANCHE = 33;


    public function __construct(
        private RouterInterface $router,
        private RequestStack $requestStack,
        private ServiceServiceEntreprise $serviceEntreprise,
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public function supprimer($entityObject, $entityIndex)
    {
        switch ($entityIndex) {
            case self::FINANCE_PAIEMENT_TAXE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_COMPTE_BANCAIRE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_PAIEMENT:
                //$this->supprimerEntiteSingleton($entityObject);
                $this->supprimerPaiement($entityObject);
                break;

            case self::FINANCE_PAIEMENT_COMMISSION:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_PAIEMENT_PARTENAIRE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_MONNAIE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_FACTURE: //Il faut supprimer les données filles
                //$this->supprimerEntiteSingleton($entityObject);
                $this->supprimerFacture($entityObject);
                break;

            case self::FINANCE_ELEMENT_FACTURE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_CONTACT:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_ENGIN:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::FINANCE_TAXE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_POLICE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PAREMETRE_UTILISATEUR: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_ASSUREUR: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_PARTENAIRE: //Il faut supprimer les données filles
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_PRODUIT:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_TRANCHE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_CHARGEMENT:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PRODUCTION_CLIENT:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_COMMENTAIRE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_ETAPE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_VICTIME:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::SINISTRE_SINISTRE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_FEEDBACK:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_ACTION:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_ETAPE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_PISTE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::CRM_COTATION:

                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::BIBLIOTHEQUE_PIECE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::BIBLIOTHEQUE_CATEGORIE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::BIBLIOTHEQUE_CLASSE:
                $this->supprimerEntiteSingleton($entityObject);
                break;

            case self::PAREMETRE_ENTREPRISE:
                $this->supprimerEntreprise($entityObject);
                break;

            default:
                dd("Cette fonction n'est pas encore disponible.");
                break;
        }
    }

    public function supprimerEntiteSingleton($entityInstance)
    {
        try {
            $this->entityManager->remove($entityInstance);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            // dd("Erreur:", $th);
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer cet enregistrement car il est déjà utilisé dans une ou plusières rubriques. Cette suppression violeraît les restrictions relatives à la sécurité des données.";
            $this->afficherFlashMessage("danger", $message);
        }
    }

    public function supprimerPolice($entityInstance)
    {
        try {
            $this->entityManager->remove($entityInstance);
            $this->entityManager->flush();
        } catch (\Throwable $th) {
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer cet enregistrement car il est déjà utilisé dans une ou plusières rubriques. Cette suppression violeraît les restrictions relatives à la sécurité des données.";
            $this->afficherFlashMessage("danger", $message);
        }
    }


    public function supprimerEntreprise($entityInstance)
    {
        try {
            $isAdmin = $entityInstance->getUtilisateur() == $this->serviceEntreprise->getUtilisateur();
            //dd($isAdmin);
            if ($isAdmin == true) {
                $this->activerContrainteIntegrite(true);

                //destruction du CRM
                $this->detruireCRM();
                $this->detruirePRODUCTION();
                $this->detruireFINANCES();
                $this->detruireSINISTRE();
                $this->detruireBIBLIOTHEQUE();

                //destruction de l'utilisateur et de son entreprise
                $this->entityManager->remove($this->serviceEntreprise->getUtilisateur());
                $this->entityManager->remove($this->serviceEntreprise->getEntreprise());
                $this->entityManager->flush();

                $this->activerContrainteIntegrite(false);

                //Il faut detruire la session de cet utilisateur, sinon c'est une erreur qui sera créée
                $session = new Session();
                $session->invalidate();

                //$this->afficherFlashMessage("success", "Suppression effectuée ave succès!");

                //On rentre sur la page de login après la destruction de l'entreprise et toutes ses données y compris l'utilisateur 
                //$url = $this->router->generate('security.login');
                //dd($url);
                //return new RedirectResponse("http://127.0.0.1:8000/connexion"); // ce code ne marche pas!
                //dd("la redirection ne marche pas!");
            } else {
                $message = "Désolé " . $this->serviceEntreprise->getUtilisateur()->getNom() . ", seul l'administrateur peut supprimer cette entreprise.";
                $this->afficherFlashMessage("danger", $message);
            }
        } catch (\Throwable $th) {
            dd($th);
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer cet enregistrement car il est déjà utilisé dans une ou plusières rubriques. Cette suppression violeraît les restrictions relatives à la sécurité des données.";
            $this->afficherFlashMessage("danger", $message);
        }
    }

    public function supprimerPaiement(Paiement $paiement)
    {
        try {
            $this->activerContrainteIntegrite(true);
            /** @var Paiement */
            foreach ($paiement->getDocuments() as $document) {
                $this->entityManager->remove($document);
            }
            $this->entityManager->remove($paiement);
            $this->entityManager->flush();

            $this->activerContrainteIntegrite(false);
        } catch (\Throwable $th) {
            dd($th);
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer cet enregistrement car il est déjà utilisé dans une ou plusières rubriques. Cette suppression violeraît les restrictions relatives à la sécurité des données.";
            $this->afficherFlashMessage("danger", $message);
        }
    }

    public function supprimerFacture(Facture $facture)
    {
        //dd($facture);
        try {
            $this->activerContrainteIntegrite(true);

            //Il faut aussi modifier les paiements qui sont éventuellement liés à cette facture
            //Il faut les détacher de ce paiement que nous allons supprimer.
            $paiements = $this->entityManager->getRepository(Paiement::class)->findBy(
                [
                    'entreprise' => $this->serviceEntreprise->getEntreprise(),
                    'facture' => $facture->getId()
                ]
            );
            foreach ($paiements as $paiement) {
                if ($paiement->getFacture()->getId() == $facture->getId()) {
                    $paiement->setFacture(null);
                    $this->entityManager->persist($paiement);
                    $this->entityManager->flush();
                }
            }
            //On supprime les élements facture
            foreach ($facture->getElementFactures() as $elementfacture) {
                $this->entityManager->remove($elementfacture);
                $this->entityManager->flush();
            }
            //On détruire maintenant la facture.
            $this->entityManager->remove($facture);
            $this->entityManager->flush();

            $this->activerContrainteIntegrite(false);
        } catch (\Throwable $th) {
            //dd($th);
            $message = $this->serviceEntreprise->getUtilisateur()->getNom() . ", Il n'est pas possible de supprimer cet enregistrement car il est déjà utilisé dans une ou plusières rubriques. Cette suppression violeraît les restrictions relatives à la sécurité des données.";
            $this->afficherFlashMessage("danger", $message);
        }
    }

    private function detruireCRM()
    {
        //Suppression des Missions / Actions dans CRM
        $this->detruireEntites($this->entityManager->getRepository(ActionCRM::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        //Suppression des Feedback dans CRM
        $this->detruireEntites($this->entityManager->getRepository(FeedbackCRM::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        //Suppression des Cotation dans CRM
        $this->detruireEntites($this->entityManager->getRepository(Cotation::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        //Suppression des Etape dans CRM
        $this->detruireEntites($this->entityManager->getRepository(EtapeCrm::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        //Suppression des Piste dans CRM
        $this->detruireEntites($this->entityManager->getRepository(Piste::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
    }


    private function detruirePRODUCTION()
    {
        $this->detruireEntites($this->entityManager->getRepository(Assureur::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Automobile::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Contact::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Client::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Partenaire::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Police::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Produit::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
    }

    private function detruireFINANCES()
    {
        $this->detruireEntites($this->entityManager->getRepository(Taxe::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Monnaie::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        // $this->detruireEntites($this->entityManager->getRepository(PaiementCommission::class)->findBy(
        //     ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        // ));
        // $this->detruireEntites($this->entityManager->getRepository(PaiementPartenaire::class)->findBy(
        //     ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        // ));
        // $this->detruireEntites($this->entityManager->getRepository(PaiementTaxe::class)->findBy(
        //     ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        // ));
    }

    private function detruireSINISTRE()
    {
        // $this->detruireEntites($this->entityManager->getRepository(CommentaireSinistre::class)->findBy(
        //     ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        // ));
        $this->detruireEntites($this->entityManager->getRepository(EtapeSinistre::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Expert::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Sinistre::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(Victime::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
    }

    private function detruireBIBLIOTHEQUE()
    {
        $this->detruireEntites($this->entityManager->getRepository(DocCategorie::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(DocClasseur::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
        $this->detruireEntites($this->entityManager->getRepository(DocPiece::class)->findBy(
            ['entreprise' => $this->serviceEntreprise->getEntreprise()]
        ));
    }

    private function detruireEntites($liste)
    {
        foreach ($liste as $entite) {
            $this->detruireEntite($entite);
        }
    }

    private function detruireEntite($entityInstance): string
    {
        $deleted = false;
        try {
            //Delete here
            $this->entityManager->remove($entityInstance);
            $this->entityManager->flush();
            $deleted = true;
        } catch (\Throwable $th) {
            //throw $th;
            $deleted = false;
        }
        return $deleted;
    }



    public function afficherFlashMessage($type, $message)
    {
        $flashBag = $this->requestStack->getMainRequest()->getSession()->getFlashBag();
        $flashBag->add($type, $message);
    }

    public function activerContrainteIntegrite($activer)
    {
        if ($activer == true) {
            $this->entityManager->getConnection()->beginTransaction();
            $this->entityManager->getConnection()->executeStatement("SET FOREIGN_KEY_CHECKS = 0");
        } else {
            $this->entityManager->getConnection()->executeStatement("SET FOREIGN_KEY_CHECKS = 1");
            $this->entityManager->getConnection()->commit();
        }
    }
}
