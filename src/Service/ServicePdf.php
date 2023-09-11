<?php

namespace App\Service;

// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class ServicePdf
{
    public ?Dompdf $dompdf;
    public ?Options $pdfOptions; // = new Options();

    public function __construct()
    {
        // Configure Dompdf according to your needs
        
        // Instantiate Dompdf with our options
        
    }

    public function openFacture()
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml('hello world');
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser
        $dompdf->stream();

        /* return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        ); */
    }
}
