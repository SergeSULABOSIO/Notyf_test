<?php

namespace App\Service;

// Include Dompdf required namespaces

use App\Entity\Facture;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class ServicePdf
{
    public function __construct()
    {
    }

    public function openFacture()
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml('hello world');
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait'); // ou 'landscape'
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser
        $dompdf->stream();

        /* $dompdf->stream("mypdf.pdf", [
            "attachment" => false
        ]); */
    }

    public function genererFacture(?Facture $facture)
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml('hello world');
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait'); // ou 'landscape'
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser (force download)
        $dompdf->stream("facture.pdf", [
            "attachment" => true
        ]);
    }
}
