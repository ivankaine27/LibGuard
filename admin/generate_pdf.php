<?php
require_once('tcpdf/tcpdf.php'); // Make sure to provide the correct path to the TCPDF library file

// Function to generate the PDF content
function generatePDF() {
    ob_start(); // Start output buffering

    // Include necessary files
    include 'includes/session.php';
    include 'includes/header.php';
    include 'includes/conn.php';

    echo '<body class="hold-transition skin-maroon-gold sidebar-mini">';
    echo '<div class="wrapper">';
    include 'includes/navbar.php';
    include 'includes/menubar.php';
    echo '<div class="content-wrapper">';
    echo '<section class="content">';
    echo '<div class="container">';
    echo '<h3>Yearly Library Book Report</h3>';

    // Output the HTML content for the book report
    echo '<div class="box-header with-border">';
    echo '<button onclick="printPage()" class="btn btn-primary">Download Report</button>';
    echo '</div>';
    echo '<div class="returned-books-container">';
    echo '<div class="box">';
    echo '<div class="box-body">';
    echo '<h4>Returned Books</h4>';
    echo '<table class="table table-bordered">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Date</th>';
    echo '<th>Student ID</th>';
    echo '<th>Name</th>';
    echo '<th>ISBN</th>';
    echo '<th>Title</th>';
    echo '<th>Status</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    includeReturnedBooks();
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="not-returned-books-container">';
    echo '<div class="box">';
    echo '<div class="box-body">';
    echo '<h4>Not Returned Books</h4>';
    echo '<table class="table table-bordered">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Date</th>';
    echo '<th>Student ID</th>';
    echo '<th>Name</th>';
    echo '<th>ISBN</th>';
    echo '<th>Title</th>';
    echo '<th>Status</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    includeNotReturnedBooks();
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="dates-container">';
    includeBookReports();
    echo '</div>';
    echo '</div>';
    echo '</section>';
    echo '</div>';
    echo '</div>';
    echo '<script>';
    echo 'function printPage() { window.print(); }';
    echo '</script>';
    echo '</body>';
    echo '</html>';

    // Get the content from the buffer and clean the buffer
    $content = ob_get_clean();
    return $content;
}

// Function to include returned books
function includeReturnedBooks() {
    global $conn;
    $sql_returned = "SELECT *, students.student_id AS stud, borrow.status AS barstat FROM borrow LEFT JOIN students ON students.id=borrow.student_id LEFT JOIN books ON books.id=borrow.book_id WHERE borrow.status = 1 ORDER BY date_borrow DESC";
    $query_returned = $conn->query($sql_returned);
    while ($row = $query_returned->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>";
        echo "<td>" . $row['stud'] . "</td>";
        echo "<td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>";
        echo "<td>" . $row['isbn'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td><span class='label label-success'>Returned</span></td>";
        echo "</tr>";
    }
}

// Function to include not returned books
function includeNotReturnedBooks() {
    global $conn;
    $sql_not_returned = "SELECT *, students.student_id AS stud, borrow.status AS barstat FROM borrow LEFT JOIN students ON students.id=borrow.student_id LEFT JOIN books ON books.id=borrow.book_id WHERE borrow.status = 0 ORDER BY date_borrow DESC";
    $query_not_returned = $conn->query($sql_not_returned);
    while ($row = $query_not_returned->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>";
        echo "<td>" . $row['stud'] . "</td>";
        echo "<td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>";
        echo "<td>" . $row['isbn'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td><span class='label label-danger'>Not Returned</span></td>";
        echo "</tr>";
    }
}

// Function to include book reports
function includeBookReports() {
    global $conn;
    $sql_dates = "SELECT DISTINCT DATE(date_borrow) AS borrow_date FROM borrow ORDER BY borrow_date DESC";
    $query_dates = $conn->query($sql_dates);
    while ($date_row = $query_dates->fetch_assoc()) {
        $date = $date_row['borrow_date'];
        echo "<div class='box'>";
        echo "<div class='box-body'>";
        echo "<div class='dates-container'>";
        echo "<h4>Date: $date</h4>";
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>Date</th><th>Student ID</th><th>Name</th><th>ISBN</th><th>Title</th><th>Status</th></tr></thead>";
        echo "<tbody>";
        $sql_books = "SELECT *, students.student_id AS stud, borrow.status AS barstat FROM borrow LEFT JOIN students ON students.id=borrow.student_id LEFT JOIN books ON books.id=borrow.book_id WHERE DATE(date_borrow) = '$date' ORDER BY date_borrow DESC";
        $query_books = $conn->query($sql_books);
        while ($book_row = $query_books->fetch_assoc()) {
            $status_label = $book_row['barstat'] ? "<span class='label label-success'>Returned</span>" : "<span class='label label-danger'>Not Returned</span>";
            echo "<tr>";
            echo "<td>" . date('M d, Y', strtotime($book_row['date_borrow'])) . "</td>";
            echo "<td>" . $book_row['stud'] . "</td>";
            echo "<td>" . $book_row['firstname'] . ' ' . $book_row['lastname'] . "</td>";
            echo "<td>" . $book_row['isbn'] . "</td>";
            echo "<td>" . $book_row['title'] . "</td>";
            echo "<td>" . $status_label . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
        echo "</div>"; // Close dates-container
        echo "</div>"; // Close box-body
        echo "</div>"; // Close box
    }
}

// Generate the PDF
$pdfContent = generatePDF();

// Create a new TCPDF instance
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Creator');
$pdf->SetTitle('Yearly Library Book Report');

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Write the HTML content
$pdf->writeHTML($pdfContent, true, false, true, false, '');

// Close and output PDF
$pdf->Output('yearly_library_book_report.pdf', 'I');

?>
