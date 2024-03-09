<?php
// Get filename from query parameter
$filename = $_GET['filename'];
echo "Downloading file: " . $filename . "<br>";


// Set headers for PDF download
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filename));

// Output PDF file for download
readfile($filename);

// Delete temporary PDF file
unlink($filename);
?>
