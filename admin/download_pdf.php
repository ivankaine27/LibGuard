<?php
// Script to handle the download of the generated PDF file

// Ensure proper file path
$pdfFilePath = 'yearly_library_book_report.pdf';

// Check if the file exists
if (file_exists($pdfFilePath)) {
    // Set headers for PDF download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($pdfFilePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($pdfFilePath));
    // Clear system output buffer
    ob_clean();
    // Flush system output buffer
    flush();
    // Read the file and output the content
    readfile($pdfFilePath);
    // Exit the script
    exit;
} else {
    // If the file does not exist, display an error message
    echo 'File not found.';
}
?>
