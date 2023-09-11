<?php

namespace App\Service;

use DateTime;
use DateInterval;
use DateTimeImmutable;
// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

class ServicePdf
{
    public ?Dompdf $dompdf;
    public ?Options $pdfOptions; // = new Options();

    public function __construct()
    {
        // Configure Dompdf according to your needs
        $this->pdfOptions = new Options();
        $this->pdfOptions->set('defaultFont', 'Arial');
        // Instantiate Dompdf with our options
        $this->dompdf = new Dompdf($this->pdfOptions);
    }

    public function openFacture()
    {
        $this->dompdf->loadHtml('hello world');
        // (Optional) Setup the paper size and orientation
        $this->dompdf->setPaper('A4', 'landscape');
        // Render the HTML as PDF
        $this->dompdf->render();
        // Output the generated PDF to Browser
        $this->dompdf->stream();
    }
}
