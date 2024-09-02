<?php

/*
*
*   @author: Amélie
*
*/

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Routing\RouterInterface;

class InvoiceGenerator
{
    private RouterInterface $router;
    private String $pdfDirectory;


    public function __construct(RouterInterface $router, String $pdfDirectory)
    {
        $this->router = $router;
        $this->pdfDirectory = $pdfDirectory;
    }

    public function generateInvoice(
        string $firstname,
        string $lastname,
        string $phone,
        string  $street,
        string $city,
        string $zipcode,
        $type,
        string $price,
        string $description,
        $operationCreatedAt,
        string $operationID
    ): Dompdf {

        // Generate the url of the images
        $logoUrl = 'http://localhost/filrouge/public/assets/black_logo2.png';
        $logo = $this->imageToBase64($logoUrl);

        $spongyUrl = 'http://localhost/filrouge/public/assets/images/spongy_proud.png';
        $spongy = $this->imageToBase64($spongyUrl);

        // instantiate and use the dompdf class
        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $dompdf->setOptions($options);
        // use the Twig environment to render the twig template
        $twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader([__DIR__ . '/../../templates']));

        // Get date of invoice generation and format it
        $invoiceCreatedAt = new \DateTime('now');
        $invoiceCreatedAt = $invoiceCreatedAt->format('d-m-Y H:i');
        $operationFinishedAt = new \DateTime('now');
        $operationFinishedAt = $operationFinishedAt->format('d-m-Y H:i');

  /*       // Calculer la durée de l'opération
        $operationDuration = $operationCreatedAt->diff($operationFinishedAt);
        $operationDurationInHours = $operationDuration->h + ($operationDuration->days * 24); // Convertir en heures si nécessaire
        // Formatage de la durée de l'opération
        $operationDurationFormatted = $operationDuration->format('%d jours, %h heures'); */

        // Ajouter 7 jours à la date actuelle
        $dueDate = new \DateTime('now');
        $dueDate->modify('+30 days');
        $formattedDueDate = $dueDate->format('d-m-Y H:i');

        // calcul du prix HT
        $tva = 10;
        $ht = self::calculateHT($price, $tva);

        // Render the HTML content from Twig template
        $htmlContent = $twig->render('attachment/invoice.html.twig', [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'phone' => $phone,
            'street' => $street,
            'city' => $city,
            'zipcode' => $zipcode,
            'type' => $type,
            'TTC' => $price,
            'HT' => $ht,
            'TVA' => $tva,
            'description' => $description,
            'logo' => $logo,
            'spongy' => $spongy,
            'operationCreatedAt' => $operationCreatedAt,
            'operationFinishedAt' => $operationFinishedAt,
            // 'operationDuration' => $operationDurationFormatted,
            'invoiceCreatedAt' => $invoiceCreatedAt,
            'dueAt' => $formattedDueDate,
            'operationID' => $operationID
        ]);

        // Load HTML content into Dompdf
        $dompdf->loadHtml($htmlContent);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        $pdf = $dompdf->output();

        // Enregistrer l'URL du PDF
        $pdfUrl = $this->generatePdfUrl($operationID);

        // Enregistrer le contenu du PDF dans le répertoire spécifié
        file_put_contents($pdfUrl, $pdf);

        return $dompdf;
    }

    // Fonction pour enregistrer l'URL du PDF dans la base de données
    public function generatePdfUrl(string $operationID): string
    {
        // Générer un nom de fichier unique pour le PDF
        $pdfFilename = 'facture_' . $operationID . '.pdf';
        $pdfUrl = $this->pdfDirectory . '/' . $pdfFilename;

        // Retourner l'URL du PDF enregistré
        return $pdfUrl;
    }

    private function imageToBase64($path)
    {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    public function calculateHT(float $ttc, float $tva): float
    {
        $tva = $tva / 100;
        $ht = $ttc / (1 + $tva);
        $ht = round($ht, 2);
        return $ht;
    }
}
