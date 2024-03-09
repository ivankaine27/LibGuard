<?php
// Include dompdf library
require_once ('C:\Users\Ivan Kaine\vendor\autoload.php');

use Dompdf\Dompdf;

// Get HTML content from POST request
$htmlContent = $_POST['html_content'];

// Initialize dompdf class
$dompdf = new Dompdf();

// Load HTML content
$dompdf->loadHtml($htmlContent);

// Render PDF
$dompdf->render();

// Output PDF as string
$pdfContent = $dompdf->output();

// Save PDF to a temporary file
$pdfFilePath = 'temp/' . uniqid() . '.pdf';
file_put_contents($pdfFilePath, $pdfContent);

// Return the file path of the generated PDF
echo $pdfFilePath;
echo "Received HTML content: " . $_POST['html_content'] . "<br>";
echo "Generated PDF file: example.pdf"; // Assuming 'example.pdf' is the generated PDF filename

?>
