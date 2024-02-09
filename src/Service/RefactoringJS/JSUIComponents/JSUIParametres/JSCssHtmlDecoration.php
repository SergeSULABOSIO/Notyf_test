<?php

namespace App\Service\RefactoringJS\JSUIComponents\JSUIParametres;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class JSCssHtmlDecoration
{
    public ?string $balise;
    public ?Collection $classesCSS;
    public ?string $contenu;

    public function __construct(?string $balise, ?string $contenu) {
        $this->balise = $balise;
        $this->contenu = $contenu;
        $this->classesCSS = new ArrayCollection();
    }

    public function ajouterClasseCss(?string $newClasseCss):?JSCssHtmlDecoration{
        if (!$this->classesCSS->contains($newClasseCss)) {
            $this->classesCSS->add($newClasseCss);
        }
        return $this;
    }

    public function retirerClasseCss(?string $oldClasseCss):?JSCssHtmlDecoration{
        $this->classesCSS->remove($oldClasseCss);
        return $this;
    }
    
    public function outputHtml():?string{
        $strclasse ="";
        foreach ($this->classesCSS as $cssclass) {
            $strclasse = $strclasse . " " . $cssclass;
        }
        $html = "<" . $this->balise ." class = '" . $strclasse . "'>" . $this->contenu . "</" . $this->balise .">";
        return $html;
    }
}
