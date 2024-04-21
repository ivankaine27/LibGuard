<?php

// Include Composer autoloader to load libraries
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Sample data
$data = array(
    array('Name', 'Email', 'Phone'),
    array('John Doe', 'john@example.com', '123-456-7890'),
    array('Jane Smith', 'jane@example.com', '987-654-3210'),
    array('Alice Johnson', 'alice@example.com', '555-555-5555')
);

// Export to PDF using dompdf
function exportToPDF($data) {
    // Create new PDF instance
    $pdf = new Dompdf();

    // Load HTML content
    $html = '<table>';
    foreach ($data as $row) {
        $html .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
    }
    $html .= '</table>';

    // Load HTML into PDF
    $pdf->loadHtml($html);

    // Set paper size and orientation
    $pdf->setPaper('A4', 'portrait');

    // Render PDF
    $pdf->render();

    // Output PDF
    $pdf->stream('export.pdf');
}

// Export to CSV
function exportToCSV($data) {
    // Set headers for CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="export.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write data to CSV
    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    // Close output stream
    fclose($output);
}

// Export to Excel using PhpSpreadsheet
function exportToExcel($data) {
    // Create new Spreadsheet instance
    $spreadsheet = new Spreadsheet();

    // Get active sheet
    $sheet = $spreadsheet->getActiveSheet();

    // Set data to cells
    foreach ($data as $rowIndex => $rowData) {
        foreach ($rowData as $columnIndex => $cellData) {
            // Convert column index to letter (e.g., 0 -> A, 1 -> B, etc.)
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);

            // Set cell value
            $sheet->setCellValue($columnLetter . ($rowIndex + 1), $cellData);
        }
    }

    // Create Excel writer
    $writer = new Xlsx($spreadsheet);

    // Save Excel file to a temporary location
    $tempFile = tempnam(sys_get_temp_dir(), 'export');
    $writer->save($tempFile);

    // Set headers for Excel (XLSX)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="export.xlsx"');
    header('Cache-Control: max-age=0');

    // Output Excel file
    readfile($tempFile);

    // Delete temporary file
    unlink($tempFile);
}

// Example usage
exportToPDF($data);
exportToCSV($data);
exportToExcel($data);

?>
