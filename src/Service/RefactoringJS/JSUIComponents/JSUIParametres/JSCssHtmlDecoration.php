<?php

namespace App\Service\RefactoringJS\JSUIComponents\JSUIParametres;

use Doctrine\Common\Collections\Collection;

class JSCssHtmlDecoration
{
    public ?string $balise;
    public ?Collection $classesCSS;
    public ?string $contenu;

    public function __construct(?string $balise, ?string $contenu) {
        $this->balise = $balise;
        $this->contenu = $contenu;
        $this->classesCSS = [];
    }

    public function ajouterClasseCss(?string $newClasseCss):?JSCssHtmlDecoration{
        foreach ($this->classesCSS as $classeCSS) {
            if($classeCSS == $newClasseCss){
                return $this;
            }
        }
        $this->classesCSS[] = $newClasseCss;
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
