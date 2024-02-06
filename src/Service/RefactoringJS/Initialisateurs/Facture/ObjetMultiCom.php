<?php

namespace App\Service\RefactoringJS\Initialisateurs\Facture;
use App\Entity\Taxe;

class ObjetMultiCom
{
    private ?bool $produireNDPrime = false;
    private ?bool $produireNDFraisGestion = false;
    private ?bool $produireNDComLocale = false;
    private ?bool $produireNDComReassurance = false;
    private ?bool $produireNDComFronting = false;
    private ?bool $produireNCRetrocommission = false;
    private ?bool $produireNCTaxeCourtier = false;
    private ?bool $produireNCTaxeAssureur = false;
    private ?Taxe $taxeCourtier;
    private ?Taxe $taxeAssureur;


    public function __construct(?Taxe $taxeCourtier = null, ?Taxe $taxeAssureur = null)
    {
        $this->taxeCourtier = $taxeCourtier;
        $this->taxeAssureur = $taxeAssureur;
    }



    /**
     * Get the value of produireNDPrime
     */ 
    public function getProduireNDPrime()
    {
        return $this->produireNDPrime;
    }

    /**
     * Set the value of produireNDPrime
     *
     * @return  self
     */ 
    public function setProduireNDPrime($produireNDPrime)
    {
        $this->produireNDPrime = $produireNDPrime;

        return $this;
    }

    /**
     * Get the value of produireNDFraisGestion
     */ 
    public function getProduireNDFraisGestion()
    {
        return $this->produireNDFraisGestion;
    }

    /**
     * Set the value of produireNDFraisGestion
     *
     * @return  self
     */ 
    public function setProduireNDFraisGestion($produireNDFraisGestion)
    {
        $this->produireNDFraisGestion = $produireNDFraisGestion;

        return $this;
    }

    /**
     * Get the value of produireNDComLocale
     */ 
    public function getProduireNDComLocale()
    {
        return $this->produireNDComLocale;
    }

    /**
     * Set the value of produireNDComLocale
     *
     * @return  self
     */ 
    public function setProduireNDComLocale($produireNDComLocale)
    {
        $this->produireNDComLocale = $produireNDComLocale;

        return $this;
    }

    /**
     * Get the value of produireNDComReassurance
     */ 
    public function getProduireNDComReassurance()
    {
        return $this->produireNDComReassurance;
    }

    /**
     * Set the value of produireNDComReassurance
     *
     * @return  self
     */ 
    public function setProduireNDComReassurance($produireNDComReassurance)
    {
        $this->produireNDComReassurance = $produireNDComReassurance;

        return $this;
    }

    /**
     * Get the value of produireNDComFronting
     */ 
    public function getProduireNDComFronting()
    {
        return $this->produireNDComFronting;
    }

    /**
     * Set the value of produireNDComFronting
     *
     * @return  self
     */ 
    public function setProduireNDComFronting($produireNDComFronting)
    {
        $this->produireNDComFronting = $produireNDComFronting;

        return $this;
    }

    /**
     * Get the value of produireNCRetrocommission
     */ 
    public function getProduireNCRetrocommission()
    {
        return $this->produireNCRetrocommission;
    }

    /**
     * Set the value of produireNCRetrocommission
     *
     * @return  self
     */ 
    public function setProduireNCRetrocommission($produireNCRetrocommission)
    {
        $this->produireNCRetrocommission = $produireNCRetrocommission;

        return $this;
    }

    /**
     * Get the value of produireNCTaxeCourtier
     */ 
    public function getProduireNCTaxeCourtier()
    {
        return $this->produireNCTaxeCourtier;
    }

    /**
     * Set the value of produireNCTaxeCourtier
     *
     * @return  self
     */ 
    public function setProduireNCTaxeCourtier($produireNCTaxeCourtier)
    {
        $this->produireNCTaxeCourtier = $produireNCTaxeCourtier;

        return $this;
    }

    /**
     * Get the value of produireNCTaxeAssureur
     */ 
    public function getProduireNCTaxeAssureur()
    {
        return $this->produireNCTaxeAssureur;
    }

    /**
     * Set the value of produireNCTaxeAssureur
     *
     * @return  self
     */ 
    public function setProduireNCTaxeAssureur($produireNCTaxeAssureur)
    {
        $this->produireNCTaxeAssureur = $produireNCTaxeAssureur;

        return $this;
    }

    /**
     * Get the value of taxeCourtier
     */ 
    public function getTaxeCourtier()
    {
        return $this->taxeCourtier;
    }

    /**
     * Get the value of taxeAssureur
     */ 
    public function getTaxeAssureur()
    {
        return $this->taxeAssureur;
    }
}
