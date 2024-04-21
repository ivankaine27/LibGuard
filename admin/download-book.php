<?php 
    include 'includes/session.php';
    include 'includes/header.php';
?>

<body class="hold-transition skin-maroon-gold sidebar-mini">
    <div class="wrapper">
        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>
        <div class="content-wrapper">
            <section class="content">
                <div class="container">
                    <h3>Yearly Library Book Report</h3>
                    <style>
                        @media print {
                            .box {
                                page-break-: always;
                            }
                        }
                    </style>
                    <div class="box-header with-border">
                        <!-- <button id="downloadPdf" class="btn btn-primary">Download Report</button> -->
                        <button class="btn btn-primary btn-sm" id="excel-btn">
                            <i class="fa fa-download"></i> Excel
                        </button>
                        <button class="btn btn-primary btn-sm" id="csv-btn">
                            <i class="fa fa-download"></i> CSV
                        </button>
                        <button class="btn btn-primary btn-sm" id="pdf-btn">
                            <i class="fa fa-download"></i> PDF
                        </button>
                    </div>

                    <?php
                        if ($_GET['pending_book_returns'] != 0) {
                    ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box printable-table">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <canvas id="chartContainer" style="height: 250px; margin-left: auto; margin-right: auto;"></canvas>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                            <?php
                                                // Fetch data for pie chart
                                                $publishQuery = "SELECT
                                                                    YEAR(books.publish_date) AS publish_year,
                                                                    COUNT(*) AS count,
                                                                    students.course_id as course_id,
                                                                    course.*,
                                                                    borrow.*
                                                                FROM
                                                                    borrow
                                                                    LEFT JOIN books ON borrow.book_id = books.id
                                                                    LEFT JOIN students ON borrow.student_id = students.id
                                                                    LEFT JOIN course ON students.course_id = course.id";
                                                                    
                                                $selected_category = isset($_GET['selected_category']) ? $_GET['selected_category'] : null;
                                                $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                                                $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

                                                // if ($selected_category !== null) {
                                                //     $publishCategories = explode(',', $selected_category);
                                                //     $publishCategories = array_map('intval', $publishCategories);
                                                //     $publishCategoriesString = implode(',', $publishCategories);

                                                //     $publishQuery .= " WHERE students.course_id IN ($publishCategoriesString)";
                                                // }

                                                // Add the date range condition
                                                if ($startDate && $endDate) {
                                                    $publishQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                                }

                                                $publishQuery .= " AND borrow.status = 0";
                                                $publishQuery .= " GROUP BY date_borrow";
                                                $publishResult = $conn->query($publishQuery);

                                                // Fetch data for each year and create tables for pending book returns
                                                $totalTransactions = 0;
                                                while ($publishRow = $publishResult->fetch_assoc()) {
                                                    $course = $publishRow['date_borrow'];
                                                    $count = $publishRow['count'];
                                                    $totalTransactions += $count;

                                                    // Output table for pending book returns for each course
                                                    echo "<div class='row'>";
                                                    echo "<div class='col-xs-12'>";
                                                    echo "<div class='box'>";
                                                    echo "<div class='box-header with-border'>";
                                                    echo "<h4><b>Pending Book Returns Data for $course</b></h4>";
                                                    echo "</div>";
                                                    echo "<div class='box-body'>";
                                                    echo "<table class='table table-bordered pending-book-table'>";
                                                    echo "<thead>";
                                                    echo "<tr>";
                                                    echo "<th>Date Borrowed</th>";
                                                    echo "<th>Date Returned</th>";
                                                    echo "<th>Student ID</th>";
                                                    echo "<th>Name</th>";
                                                    echo "<th>ISBN</th>";
                                                    echo "<th>Title</th>";
                                                    echo "<th>Status</th>";
                                                    echo "</tr>";
                                                    echo "</thead>";
                                                    echo "<tbody>";

                                                    // Fetch data for the specific year for pending book returns
                                                    $yearQuery = "SELECT
                                                                    DISTINCT b.id,
                                                                    b.*,
                                                                    r.date_return AS return_date,
                                                                    students.student_id AS stud,
                                                                    students.firstname,
                                                                    students.lastname,
                                                                    books.isbn,
                                                                    books.title,
                                                                    books.author
                                                                FROM
                                                                    borrow b
                                                                    LEFT JOIN returns r ON b.book_id = r.book_id
                                                                    LEFT JOIN students ON students.id = b.student_id
                                                                    LEFT JOIN books ON books.id = b.book_id
                                                                    LEFT JOIN course ON students.course_id = course.id
                                                                WHERE
                                                                    students.course_id = {$publishRow['course_id']}
                                                                    AND b.status = 0
                                                                    OR r.date_return IS NULL";

                                                    // Add the date range condition
                                                    if ($startDate && $endDate) {
                                                        $yearQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                                    }

                                                    $yearQuery .= " ORDER BY b.date_borrow DESC";

                                                    $yearResult = $conn->query($yearQuery);

                                                    if ($yearResult->num_rows > 0) {
                                                        while ($row = $yearResult->fetch_assoc()) {
                                                            $status = ($row['status']) ? '<span class="label label-success">returned</span>' : '<span class="label label-danger">not returned</span>';
                                                            echo "<tr>";
                                                            echo "<td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>";
                                                            echo "<td>" . ($row['return_date'] ? date('M d, Y', strtotime($row['return_date'])) : "Not Returned Yet") . "</td>";
                                                            echo "<td>" . $row['stud'] . "</td>";
                                                            echo "<td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>";
                                                            echo "<td>" . $row['isbn'] . "</td>";
                                                            echo "<td>" . $row['title'] . "</td>";
                                                            echo "<td>" . $status . "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='7'>No Pending Book Returns for Books Published in $course</td></tr>";
                                                    }

                                                    echo "</tbody>";
                                                    echo "</table>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                }
                                                ?>
                                                <div class="box-header with-border">Total Borrow Transactions: <?php echo $totalTransactions; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="not-returned-books-container pending-book-returns">
                            <div class="box printable-table">
                                <div class="box-body">
                                    <h4>Not Returned Books</h4>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>ISBN</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php includeNotReturnedBooks(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> -->
                    <?php 
                        }
                    ?>

                    <?php
                        if ($_GET['books_borrowed_and_returned'] != 0) {
                    ?>
                        <div class="returned-books-container books-borrowed-and-returned">
                            <div class="box printable-table">
                                <div class="box-body">
                                    <canvas id="returnedChartContainer" style="height: 250px; margin-left: auto; margin-right: auto;"></canvas>
                                    <h4>Returned Books</h4>
                                    <table class="table table-bordered book-borrowed-and-return-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>ISBN</th>
                                                <th>Title</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php includeReturnedBooks(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Container for Dates -->
      
                        <div class="dates-container printable-table">
                            <?php includeBookReports(); ?>
                        </div>
                    <?php 
                        }
                    ?>

                    <?php
                        if ($_GET['contrast_books_returned_and_pending_returns'] != 0) {
                    ?>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                            //! FIX ME
                                            <canvas id="borrowReturnChartContainer" style="height: 250px; margin-left: auto; margin-right: auto;"></canvas>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                            <?php
                                                // Fetch data for book borrowed and returned
                                                $returnedQuery = "SELECT DISTINCT b.id, b.*, books.isbn, books.title, books.author, YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                                                            FROM borrow b
                                                                            LEFT JOIN books ON books.id = b.book_id";

                                                // if ($selected_category !== null) {
                                                //     $returnedQuery .= " WHERE YEAR(books.publish_date) IN ($publishCategoriesString)";
                                                // }

                                                // Add the date range condition
                                                if ($startDate && $endDate) {
                                                    $returnedQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                                }
                                                $returnedQuery .= " AND b.status = 1";
                                                $returnedQuery .= " GROUP BY publish_year";
                                                $returnedResult = $conn->query($returnedQuery);

                                                // Fetch data for each year and create tables for book borrowed and returned
                                                $totalTransactionsReturned = 0;
                                                while ($returnedRow = $returnedResult->fetch_assoc()) {
                                                    // $year = $returnedRow['publish_year'];
                                                    $count = $returnedRow['count'];
                                                    $totalTransactionsReturned += $count;

                                                    // Output table for book borrowed and returned for each year
                                                    echo "<div class='row'>";
                                                    echo "<div class='col-xs-12'>";
                                                    echo "<div class='box'>";
                                                    echo "<div class='box-header with-border'>";
                                                    echo "<h4><b>Book Borrowed and Returned Data</b></h4>";
                                                    echo "</div>";
                                                    echo "<div class='box-body'>";
                                                    echo "<table class='table table-bordered'>";
                                                    echo "<thead>";
                                                    echo "<tr>";
                                                    echo "<th>Date Borrowed</th>";
                                                    echo "<th>Date Returned</th>";
                                                    echo "<th>Student ID</th>";
                                                    echo "<th>Name</th>";
                                                    echo "<th>ISBN</th>";
                                                    echo "<th>Title</th>";
                                                    echo "</tr>";
                                                    echo "</thead>";
                                                    echo "<tbody>";

                                                    // Fetch data for the specific year for book borrowed and returned
                                                    $returnedYearQuery = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                                                    FROM borrow b
                                                    LEFT JOIN returns r ON b.book_id = r.book_id
                                                    LEFT JOIN students ON students.id = b.student_id
                                                    LEFT JOIN books ON books.id = b.book_id";

                                                    // Add the date range condition
                                                    if ($startDate && $endDate) {
                                                        $returnedYearQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                                    }

                                                    $returnedYearQuery .= " ORDER BY b.date_borrow DESC";

                                                    $returnedYearResult = $conn->query($returnedYearQuery);

                                                    if ($returnedYearResult->num_rows > 0) {
                                                        while ($row = $returnedYearResult->fetch_assoc()) {
                                                            echo "<tr>";
                                                            echo "<td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>";
                                                            echo "<td>" . ($row['return_date'] ? date('M d, Y', strtotime($row['return_date'])) : "Not Returned Yet") . "</td>";
                                                            echo "<td>" . $row['stud'] . "</td>";
                                                            echo "<td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>";
                                                            echo "<td>" . $row['isbn'] . "</td>";
                                                            echo "<td>" . $row['title'] . "</td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6'>No Book Borrowed and Returned Transactions for Books Published in $year</td></tr>";
                                                    }

                                                    echo "</tbody>";
                                                    echo "</table>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                }
                                            ?>

                                                <div class="box-header with-border">Total Book Borrowed and Returned Transactions: <?php echo $totalTransactionsReturned; ?></div>
                                                <br>
                                            <?php
                                                echo "<p>This table displays the book borrowing and returning transactions according to the publish years of books. It includes details such as the date borrowed, date returned (if returned), student ID, student name, ISBN, and title of the book.</p>";
                                                // Report data analytics
                                                $borrowPercentage = ($totalTransactionsReturned > 0) ? (($totalTransactions / $totalTransactionsReturned) * 100) : 0;
                                                echo "<p>The total borrow transactions are: $totalTransactions, while the total book borrowed and returned transactions are: $totalTransactionsReturned.</p>";
                                                echo "<p>In the selected date range, all the total book borrowing transactions are $totalTransactions. This constitutes a borrow percentage of $borrowPercentage%.</p>";
                                                // Additional data analytics reports
                                                // Example: Average number of books borrowed per student
                                                $avgBooksBorrowedPerStudentQuery = "SELECT AVG(num_books) AS avg_books_borrowed_per_student FROM (SELECT COUNT(*) AS num_books FROM borrow GROUP BY student_id) AS subquery";
                                                $avgBooksBorrowedPerStudentResult = $conn->query($avgBooksBorrowedPerStudentQuery);
                                                $avgBooksBorrowedPerStudentRow = $avgBooksBorrowedPerStudentResult->fetch_assoc();
                                                $avgBooksBorrowedPerStudent = $avgBooksBorrowedPerStudentRow['avg_books_borrowed_per_student'];

                                                echo "<div class='box-header with-border'>Average Number of Books Borrowed per Student: $avgBooksBorrowedPerStudent</div>";
                                                echo "<br>";

                                                // Example: Most borrowed book
                                                $mostBorrowedBookQuery = "SELECT books.title AS most_borrowed_book, COUNT(*) AS borrow_count FROM borrow LEFT JOIN books ON borrow.book_id = books.id GROUP BY borrow.book_id ORDER BY borrow_count DESC LIMIT 1";
                                                $mostBorrowedBookResult = $conn->query($mostBorrowedBookQuery);
                                                $mostBorrowedBookRow = $mostBorrowedBookResult->fetch_assoc();
                                                $mostBorrowedBook = $mostBorrowedBookRow['most_borrowed_book'];
                                                $mostBorrowedBookCount = $mostBorrowedBookRow['borrow_count'];

                                                echo "<div class='box-header with-border'>Most Borrowed Book: $mostBorrowedBook (Borrow Count: $mostBorrowedBookCount)</div>";
                                                echo "<br>";
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php 
                        }
                    ?>

                    <?php
                        if ($_GET['all_transaction_history'] != 0) {
                    ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">All Book Transactions</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="box-body">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                        <canvas id="bookTransactionsChart" style="height: 250px; width: 900px; margin-left: auto; margin-right: auto;"></canvas>
                                                            <table class='table table-bordered all-transaction-history-table' style="display: none;">
                                                            <thead>
                                                                <tr>
                                                                    <th>Name</th>
                                                                    <th>Data</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Category 1</td>
                                                                    <td>10</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        }
                    ?>

                    <?php
                        if ($_GET['rankings_per_total_transaction'] != 0) {
                    ?>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Rankings of Book</h3>
                                    </div>
                                    <div class="row">
                                        <div class="box-body ranking-of-book-publish-year-by-total-transaction-table">
                                            <?php
                                                $selected_publish = isset($_GET['selected_publish']) ? $_GET['selected_publish'] : null;
                                                $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                                                $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

                                                // Fetch data for all borrowers grouped by publish year
                                                $borrowersQuery = "SELECT COUNT(*) AS total_borrowers, b.*
                                                                    FROM borrow b
                                                                    LEFT JOIN books ON b.book_id = books.id
                                                                    WHERE b.status IN (0, 1)"; // Include both returned and not returned
                                                if ($startDate && $endDate) {
                                                    $borrowersQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                                }
                                                $borrowersQuery .= " GROUP BY date_borrow";
                                                $borrowersResult = $conn->query($borrowersQuery);

                                                // Prepare data for CanvasJS
                                                $barChartData = array();
                                                $allYears = array(); // Store all years to check against selected publish years
                                                while ($borrowersRow = $borrowersResult->fetch_assoc()) {
                                                    $year = $borrowersRow['date_borrow'];
                                                    $totalBorrowers = $borrowersRow['total_borrowers'];
                                                    $barChartData[$year] = $totalBorrowers;
                                                    $allYears[] = $year;
                                                }

                                                // Add selected publish years with 0 transactions
                                                if (!empty($selected_publish)) {
                                                    $selectedYears = explode(',', $selected_publish);
                                                    $selectedYears = array_map('intval', $selectedYears);
                                                    foreach ($selectedYears as $year) {
                                                        if (!in_array($year, $allYears)) {
                                                            $barChartData[$year] = 0;
                                                            $allYears[] = $year;
                                                        }
                                                    }
                                                }

                                                arsort($barChartData); // Sort years based on total transactions

                                                $rank = 1;
                                                $yearsWithTransactions = [];
                                                $yearsWithNoTransactions = [];

                                                foreach ($barChartData as $year => $transactions) {
                                                    if ($transactions > 0) {
                                                        $yearsWithTransactions[] = array('year' => $year, 'transactions' => $transactions);
                                                    } else {
                                                        $yearsWithNoTransactions[] = $year;
                                                    }
                                                }

                                                // Construct the table HTML dynamically
                                                $tableHTML = '<div class="row"><div class="col-md-12"><table class="table table-bordered ranking-of-book-publish-year-by-total-transaction">';
                                                $tableHTML .= '<thead>';
                                                $tableHTML .= '<tr><th>Top</th><th>Year</th><th>Total Borrowers</th></tr>';
                                                $tableHTML .= '</thead>';
                                                $tableHTML .= '<tbody>';

                                                foreach ($yearsWithTransactions as $yearData) {
                                                    $tableHTML .= '<tr>';
                                                    $tableHTML .= '<td>' . $rank . '</td>';
                                                    $tableHTML .= '<td>' . $yearData['year'] . '</td>';
                                                    $tableHTML .= '<td>' . $yearData['transactions'] . '</td>';
                                                    $tableHTML .= '</tr>';
                                                    $rank++;
                                                }

                                                $tableHTML .= '</tbody>';
                                                $tableHTML .= '</table>';
                                                $tableHTML .= '</div>';
                                                $tableHTML .= '</div>';

                                                // Output the table HTML
                                                echo $tableHTML;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        }
                    ?>

            </section>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <!-- <script type="text/javascript" src="js/script.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.getElementById('downloadPdf').addEventListener('click', function(event) {
            event.preventDefault();
            printCurrentPage(); // Print the current page content
        });
        function printCurrentPage() {
            var originalContent = document.body.innerHTML; // Save the original content

            // Define the HTML structure for the returned books container
            var returnedBooksContainerHTML = `
            <h3>Yearly Library Book Report</h3>
            <div class="returned-books-container">
                        <div class="box printable-table">
                            <div class="box-body">
                                <h4>Returned Books</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>ISBN</th>
                                            <th>Title</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php includeReturnedBooks(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="not-returned-books-container">
                        <div class="box printable-table">
                            <div class="box-body">
                                <h4>Not Returned Books</h4>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>ISBN</th>
                                            <th>Title</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php includeNotReturnedBooks(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Container for Dates -->
      
                    <div class="dates-container printable-table">
                        <?php includeBookReports(); ?>
            `;

            document.body.innerHTML = returnedBooksContainerHTML; // Replace with the printable content

            window.print(); // Print the current page

            document.body.innerHTML = originalContent; // Restore the original content
        }
    </script>

    <?php
        if ($_GET['contrast_books_returned_and_pending_returns'] != 0) {
    ?>
        <script>
            window.onload = function () {
                // Pie chart for borrow and return transactions
                var borrowReturnData = [
                    { label: "Pending Book Returns", y: <?php echo $totalTransactions; ?> },
                    { label: "Book Borrowed and Returned", y: <?php echo $totalTransactionsReturned; ?> }
                ];

                var borrowReturnChart = new Chart(document.getElementById('borrowReturnChartContainer'), {
                    type: 'pie',
                    data: {
                        labels: borrowReturnData.map(data => data.label),
                        datasets: [{
                            data: borrowReturnData.map(data => data.y),
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        title: {
                            display: true,
                            text: 'Pending Book Returns vs Book Borrowed and Returned',
                            font: {
                                    size: 20,
                                    
                                },
                                padding: {
                                    top: 10,
                                    bottom: 30
                                }
                        },
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 15
                                }
                            },
                            label: borrowReturnData.map(data => data.label)
                        },
                    }
                });
            }
        </script>
    <?php 
        }
    ?>

    <?php
        if ($_GET['pending_book_returns'] != 0) {
    ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                <?php
                    // Fetch data for pie chart
                    $pieChartData = array();
                    $pieChartQuery = "SELECT category.*, COUNT(*) AS count, borrow.*
                                    FROM borrow
                                    LEFT JOIN books ON borrow.book_id = books.id
                                    LEFT JOIN category ON books.category_id = category.id";

                    // Add the date range condition
                    if ($startDate && $endDate) {
                        $pieChartQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                    }

                    // $pieChartQuery .= " AND borrow.status = 0";
                    $pieChartQuery .= " GROUP BY date_borrow";
                    $pieChartResult = $conn->query($pieChartQuery);

                    while ($pieChartRow = $pieChartResult->fetch_assoc()) {
                        $pieChartData[] = array(
                            "name" => $pieChartRow['date_borrow'],
                            "count" => $pieChartRow['count']
                        );
                    }

                    // Generate random background colors
                    $backgroundColor = [];
                    for ($i = 0; $i < count($pieChartData); $i++) {
                        $backgroundColor[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                    }
                ?>

                const pendingBookReturns = <?php echo json_encode($pieChartData); ?>;
                const pendingBookReturnsPublishYear = pendingBookReturns.map(element => element.name);
                const pendingBookReturnsCount = pendingBookReturns.map(element => element.count);
                const pendingBookReturnsBackgroundColor = <?php echo json_encode($backgroundColor); ?>;
                const pendingBookReturnsContainer = document.getElementById('chartContainer');

                new Chart(pendingBookReturnsContainer, {
                    type: 'pie',
                    data: {
                        labels: pendingBookReturnsPublishYear,
                        datasets: [{
                            label: pendingBookReturns[pendingBookReturnsCount],
                            data: pendingBookReturnsCount,
                            backgroundColor: pendingBookReturnsBackgroundColor,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        size: 20,
                                        weight: 'bolder'
                                    }
                                },
                                label: pendingBookReturnsPublishYear
                            },
                            title: {
                                display: true,
                                text: 'Pending Book Returns',
                                font: {
                                    size: 20,
                                    
                                },
                                padding: {
                                    top: 10,
                                    bottom: 30
                                }
                            }
                        }
                    },
                });
            });
        </script>
    <?php
        }
    ?>

    <?php
        if ($_GET['books_borrowed_and_returned'] != 0) {
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            <?php
                // Fetch data for book borrowed and returned
                $returnedChartData = array();
                $booksBorrowedAndReturnedQuery = "SELECT COUNT(*) AS count
                                FROM borrow b
                                -- INNER JOIN returns ON b.book_id = returns.book_id
                                LEFT JOIN books ON b.book_id = books.id";

                if ($selected_category !== null) {
                    $publishYears = explode(',', $selected_category);
                    $publishYearsString = implode(',', $publishYears);
                    // $booksBorrowedAndReturnedQuery .= " WHERE category.id IN ($publishYearsString)";
                }

                // Add the date range condition
                if ($startDate && $endDate) {
                    $booksBorrowedAndReturnedQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                }

                $booksBorrowedAndReturnedQuery .= " GROUP BY date_borrow";
                $booksBorrowedAndReturnedResult = $conn->query($booksBorrowedAndReturnedQuery);

                while ($returnedRow = $booksBorrowedAndReturnedResult->fetch_assoc()) {
                    $year = $returnedRow['date_borrow'];
                    $count = $returnedRow['count'];
                    $returnedChartData[] = array('name' => $year, 'count' => $count);
                }

                // Generate random background colors
                $backgroundColor = [];
                for ($i = 0; $i < count($returnedChartData); $i++) {
                    $backgroundColor[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                }
            ?>

            const bookBorrowedAndReturned = <?php echo json_encode($returnedChartData); ?>;
            const bookBorrowedAndReturnedPublishYear = bookBorrowedAndReturned.map(element => element.name);
            const bookBorrowedAndReturnedCount = bookBorrowedAndReturned.map(element => element.count);
            const bookBorrowedAndReturnedBackgroundColor = <?php echo json_encode($backgroundColor); ?>;
            const bookBorrowedAndReturnedContainer = document.getElementById('returnedChartContainer');

            new Chart(bookBorrowedAndReturnedContainer, {
                type: 'pie',
                data: {
                    labels: bookBorrowedAndReturnedPublishYear,
                    datasets: [{
                        label: 'Book Borrowed and Returned by Category',
                        data: bookBorrowedAndReturnedCount,
                        backgroundColor: bookBorrowedAndReturnedBackgroundColor,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 13,
                                    weight: 'bolder'
                                }
                            },
                            label: bookBorrowedAndReturnedPublishYear
                        },
                        title: {
                            display: true,
                            text: 'Book Borrowed and Returned Published of Books',
                            font: {
                                size: 20,
                                
                            },
                            padding: {
                                top: 10,
                                bottom: 50
                            }
                        }
                    }
                },
            });
        });
    </script>
    <?php 
        }
    ?>

    <?php
        if ($_GET['all_transaction_history'] != 0) {
    ?>
        <script>
            $(document).ready(function () {
                // Fetch data for book transactions by publish year
                <?php 
                // Initialize the array to store the data
                $data = array();

                // Execute SQL query to fetch book transactions by publish year
                $query = "SELECT category.*, COUNT(*) AS total_transactions, borrow.*
                            FROM borrow
                            LEFT JOIN books ON borrow.book_id = books.id
                            LEFT JOIN category ON books.category_id = category.id
                            GROUP BY date_borrow";

                $result = $conn->query($query);

                // Check if query executed successfully
                if ($result) {
                    // Fetch associative array
                    while ($row = $result->fetch_assoc()) {
                        // Append data to the array
                        $data[] = $row;
                    }
                    // Free result set
                    $result->free();
                }
                ?>

                const totalTransaction = <?php echo json_encode($data); ?>;
                const totalTransactionName = totalTransaction.map(element => element.date_borrow);
                const totalTransactionData = totalTransaction.map(element => element.total_transactions);

                const totalTransactionContainer = document.getElementById('bookTransactionsChart').getContext('2d');

                new Chart(totalTransactionContainer, {
                    type: 'bar',
                    data: {
                        labels: totalTransactionName,
                        datasets: [{
                            label: 'Total Transactions',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1,
                            data: totalTransactionData
                        }]
                    },
                    options: {
                        legend: {
                            position: 'right',
                            labels: {
                                fontSize: 13,
                                fontWeight: 'bolder'
                            }
                        }
                    }
                });



                // $.ajax({
                //     url: 'fetch_book_transactions.php', // Path to your PHP script to fetch data
                //     method: 'GET',
                //     success: function (data) {
                //         var years = [];
                //         var transactions = [];

                //         for (var i in data) {
                //             years.push(data[i].publish_year);
                //             transactions.push(data[i].total_transactions);
                //         }

                //         var ctx = document.getElementById('bookTransactionsChart').getContext('2d');
                //         var chart = new Chart(ctx, {
                //             type: 'bar',
                //             data: {
                //                 labels: years,
                //                 datasets: [{
                //                     label: 'Total Transactions',
                //                     backgroundColor: 'rgba(255, 99, 132, 0.2)',
                //                     borderColor: 'rgba(255, 99, 132, 1)',
                //                     borderWidth: 1,
                //                     data: transactions
                //                 }]
                //             },
                //         });
                //     },
                //     error: function (data) {
                //         console.log(data);
                //     }
                // });
            });
        </script>
    <?php
        }
    ?>

  
</body>
</html>

<?php
     function includeReturnedBooks() {
        include 'includes/conn.php';
    
        // Validate and sanitize input parameters
        $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
        $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;
        // Prepare the SQL query with the date filter
        $sql_returned = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, books.isbn, books.title, books.author, students.*
        FROM borrow b
        LEFT JOIN returns r ON b.book_id = r.book_id
        LEFT JOIN books ON books.id = b.book_id
        LEFT JOIN students ON b.student_id = students.id
                         WHERE b.status = 1";
    
        if ($startDate && $endDate) {
            $sql_returned .= " AND date_borrow BETWEEN '$startDate' AND '$endDate'";
        }

        $sql_returned .= " ORDER BY b.date_borrow DESC";
    
        // Execute the SQL query
        $query_returned = $conn->query($sql_returned);

        if ($query_returned != false) {
            // Fetch and display the results
            while ($row = $query_returned->fetch_assoc()) {
                echo "
                    <tr>
                        <td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>
                        <td>" . $row['student_id'] . "</td>
                        <td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>
                        <td>" . $row['isbn'] . "</td>
                        <td>" . $row['title'] . "</td>
                        <td><span class='label label-success'>Returned</span></td>
                    </tr>
                ";
            }
        } else {
            echo "
                <tr>
                    <td colspan='6' class='text-center'>No Data Available</td>
                </tr>
            ";
        }
    }
    

    function includeNotReturnedBooks() {
        include 'includes/conn.php';
    
        // Validate and sanitize input parameters
        $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
        $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;
    
        // Prepare the SQL query with the date filter
        $sql_not_returned = "SELECT
            DISTINCT b.id,
            b.*,
            r.date_return AS return_date,
            books.isbn,
            books.title,
            books.author,
            students.*
        FROM
            borrow b
            LEFT JOIN returns r ON b.book_id = r.book_id
            LEFT JOIN books ON books.id = b.book_id
            LEFT JOIN students ON b.student_id = students.id
        WHERE
            b.status = 0";
    
        if ($startDate && $endDate) {
            $sql_not_returned .= " AND date_borrow BETWEEN '$startDate' AND '$endDate'";
        }
    
        $sql_not_returned .= " ORDER BY date_borrow DESC";
    
        // Execute the SQL query
        $query_not_returned = $conn->query($sql_not_returned);
    
        if ($query_not_returned != false) {
            // Fetch and display the results
            while ($row = $query_not_returned->fetch_assoc()) {
                echo "
                    <tr>
                        <td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>
                        <td>" . $row['student_id'] . "</td>
                        <td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>
                        <td>" . $row['isbn'] . "</td>
                        <td>" . $row['title'] . "</td>
                        <td><span class='label label-danger'>Not Returned</span></td>
                    </tr>
                ";
            }
        } else {
            echo "
                <tr>
                    <td colspan='6' class='text-center'>No Data Available</td>
                </tr>
            ";
        }
    }

    function includeBookReports() {
        include 'includes/conn.php';
        $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
        $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;
        $sql_dates = "SELECT DISTINCT DATE(date_borrow) AS borrow_date FROM borrow WHERE";
        if ($startDate && $endDate) {
            $sql_dates .= " date_borrow BETWEEN '$startDate' AND '$endDate'";
        }
        $sql_dates .= " ORDER BY borrow_date DESC";
        $query_dates = $conn->query($sql_dates);
        while($date_row = $query_dates->fetch_assoc()) {
            $date = $date_row['borrow_date'];
            echo "<div class='box'>";
            echo "<div class='box-body'>";
            echo "<div class='dates-container'>";
            echo "<h4>Date: $date</h4>";
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>Date</th><th>Student ID</th><th>Name</th><th>ISBN</th><th>Title</th><th>Status</th></tr></thead>";
            echo "<tbody>";

            $sql_books = "SELECT
                DISTINCT b.id,
                b.*,
                r.date_return AS return_date,
                books.isbn,
                books.title,
                books.author,
                students.*
            FROM
                borrow b
                LEFT JOIN returns r ON b.book_id = r.book_id
                LEFT JOIN books ON books.id = b.book_id
                LEFT JOIN students ON b.student_id = students.id
            WHERE
                DATE(date_borrow) = '$date'
            ORDER BY
                date_borrow DESC";

            $query_books = $conn->query($sql_books);
            while($book_row = $query_books->fetch_assoc()) {
                $status_label = $book_row['status'] ? "<span class='label label-success'>Returned</span>" : "<span class='label label-danger'>Not Returned</span>";
                echo "<tr>";
                echo "<td>".date('M d, Y', strtotime($book_row['date_borrow']))."</td>";
                echo "<td>".$book_row['student_id']."</td>";
                echo "<td>".$book_row['firstname'].' '.$book_row['lastname']."</td>";
                echo "<td>".$book_row['isbn']."</td>";
                echo "<td>".$book_row['title']."</td>";
                echo "<td>".$status_label."</td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>"; // Close dates-container
            echo "</div>"; // Close box-body
            echo "</div>"; // Close box
        }
    }
    
?>

<script type="text/javascript" language="javascript">
    $(document).ready(function () {
        // Function to convert image file to base64 data URL
        function imageToDataURL(imagePath) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var reader = new FileReader();
                        reader.onload = function() {
                            resolve(reader.result);
                        };
                        reader.readAsDataURL(xhr.response);
                    } else {
                        reject(new Error('Failed to load image'));
                    }
                };
                xhr.onerror = function() {
                    reject(new Error('Network error occurred'));
                };
                xhr.open('GET', imagePath);
                xhr.responseType = 'blob';
                xhr.send();
            });
        }
        
        var imagePath = '../images/libguard-logo.png';
        var bannerWidth = 400; 
        var bannerHeight = 200;

        //Datatable variables
        var pendingBooksTable;
        var bookBorrowedAndReturnTable;
        var contrastBooksReturnedAndPendingReturns;
        var allTransactionHistory
        var rankingPerTotalTransaction;
        var table;

        imageToDataURL(imagePath)
            .then(function(dataURL) {
                var image = new Image();
                image.onload = function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');
                    canvas.width = bannerWidth;
                    canvas.height = bannerHeight;
                    ctx.drawImage(image, 0, 0, bannerWidth, bannerHeight);
                    var resizedImage = canvas.toDataURL('image/png');

                    table = $('#book_data').DataTable({
                        dom: 'lBfrtip',
                        searching: false, 
                        paging: false, 
                        info: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                className: 'buttons-excel',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                    // Add the title, student name, and student ID to the Excel document
                                    var title = 'Borrowed Books by Category';
                                    
                                    // Add title, student name, and student ID to separate rows
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr></table>';
                                    
                                    // Add an empty row for better formatting
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr></tr></table>';
                                },
                                filename: 'Borrowed Books by Category' // Set the filename for download
                            },
                            {
                                extend: 'csvHtml5',
                                className: 'buttons-csv',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (csv) {
                                    // Add the title and student information to the CSV content
                                    var csvContent = 'Borrowed Books by Category\n';

                                    // Append the existing CSV content
                                    csvContent += csv;

                                    return csvContent;
                                },
                                filename: 'Borrowed Books by Category' // Set the filename for download
                            },
                            {
                                extend: 'pdfHtml5',
                                className: 'buttons-pdf',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (doc) {
                                    // Remove the title
                                    doc.content.splice(0, 1);

                                    // Convert Chart.js chart to a base64-encoded PNG image
                                    var canvas = document.createElement('canvas');
                                    canvas.width = 300; // Adjust width as needed
                                    canvas.height = 300; // Adjust height as needed
                                    var ctx = canvas.getContext('2d');
                                    ctx.drawImage(document.getElementById('chartContainer'), 0, 0, canvas.width, canvas.height);
                                    var chartImage = canvas.toDataURL('image/png');

                                    doc.content.unshift({
                                        margin: [0, 0, 0, 12],
                                        alignment: 'center',
                                        image: chartImage,
                                    });

                                    doc.content.unshift({
                                        margin: [100, 0, 0, -50],
                                        alignment: 'center',
                                        image: resizedImage,
                                    });

                                    // Add the title and student information
                                    doc.content.splice(1, 0, {
                                        text: [
                                            { text: 'Borrowed Books by Category\n', fontSize: 14, bold: true },
                                        ],
                                        alignment: 'left',
                                        margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                                    });
                                },
                                filename: 'Borrowed Books by Category' // Set the filename for download
                            }
                        ],
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                    });

                    pendingBooksTable = $('.pending-book-table').DataTable({
                        dom: 'lBfrtip',
                        searching: false, 
                        paging: false, 
                        info: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                className: 'buttons-excel',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                    // Add the title, student name, and student ID to the Excel document
                                    var title = 'Pending Book Return';
                                    
                                    // Add title, student name, and student ID to separate rows
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr></table>';
                                    
                                    // Add an empty row for better formatting
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr></tr></table>';
                                },
                                filename: 'Pending Book Return' // Set the filename for download
                            },
                            {
                                extend: 'csvHtml5',
                                className: 'buttons-csv',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (csv) {
                                    // Add the title and student information to the CSV content
                                    var csvContent = 'Pending Book Return\n';

                                    // Append the existing CSV content
                                    csvContent += csv;

                                    return csvContent;
                                },
                                filename: 'Pending Book Return' // Set the filename for download
                            },
                            {
                                extend: 'pdfHtml5',
                                className: 'buttons-pdf',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (doc) {
                                    // Remove the title
                                    doc.content.splice(0, 1);

                                    // Convert Chart.js chart to a base64-encoded PNG image
                                    var canvas = document.createElement('canvas');
                                    canvas.width = 300; // Adjust width as needed
                                    canvas.height = 300; // Adjust height as needed
                                    var ctx = canvas.getContext('2d');
                                    ctx.drawImage(document.getElementById('chartContainer'), 0, 0, canvas.width, canvas.height);
                                    var chartImage = canvas.toDataURL('image/png');

                                    doc.content.unshift({
                                        margin: [0, 0, 0, 12],
                                        alignment: 'center',
                                        image: chartImage,
                                    });

                                    doc.content.unshift({
                                        margin: [100, 0, 0, -50],
                                        alignment: 'center',
                                        image: resizedImage,
                                    });

                                    // Add the title and student information
                                    doc.content.splice(1, 0, {
                                        text: [
                                            { text: 'Pending Book Return\n', fontSize: 14, bold: true },
                                        ],
                                        alignment: 'left',
                                        margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                                    });
                                },
                                filename: 'Pending Book Return' // Set the filename for download
                            }
                        ],
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                    });

                    bookBorrowedAndReturnTable = $('.book-borrowed-and-return-table').DataTable({
                        dom: 'lBfrtip',
                        searching: false, 
                        paging: false, 
                        info: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                className: 'buttons-excel',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                    // Add the title, student name, and student ID to the Excel document
                                    var title = 'Book Borrowed and Returned Data';
                                    
                                    // Add title, student name, and student ID to separate rows
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr></table>';
                                    
                                    // Add an empty row for better formatting
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr></tr></table>';
                                },
                                filename: 'Book Borrowed and Returned Data' // Set the filename for download
                            },
                            {
                                extend: 'csvHtml5',
                                className: 'buttons-csv',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (csv) {
                                    // Add the title and student information to the CSV content
                                    var csvContent = 'Book Borrowed and Returned Data\n';

                                    // Append the existing CSV content
                                    csvContent += csv;

                                    return csvContent;
                                },
                                filename: 'Book Borrowed and Returned Data' // Set the filename for download
                            },
                            {
                                extend: 'pdfHtml5',
                                className: 'buttons-pdf',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (doc) {
                                    // Remove the title
                                    doc.content.splice(0, 1);

                                    // Convert Chart.js chart to a base64-encoded PNG image
                                    var canvas = document.createElement('canvas');
                                    canvas.width = 300; // Adjust width as needed
                                    canvas.height = 300; // Adjust height as needed
                                    var ctx = canvas.getContext('2d');
                                    ctx.drawImage(document.getElementById('returnedChartContainer'), 0, 0, canvas.width, canvas.height);
                                    var chartImage = canvas.toDataURL('image/png');

                                    doc.content.unshift({
                                        margin: [0, 0, 0, 12],
                                        alignment: 'center',
                                        image: chartImage,
                                    });

                                    doc.content.unshift({
                                        margin: [100, 0, 0, -50],
                                        alignment: 'center',
                                        image: resizedImage,
                                    });

                                    // Add the title and student information
                                    doc.content.splice(1, 0, {
                                        text: [
                                            { text: 'Book Borrowed and Returned Data\n', fontSize: 14, bold: true },
                                        ],
                                        alignment: 'left',
                                        margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                                    });
                                },
                                filename: 'Book Borrowed and Returned Data' // Set the filename for download
                            }
                        ],
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                    });

                    contrastBooksReturnedAndPendingReturns = $('.contrast-returned-and-pending-returns-table').DataTable({
                        dom: 'lBfrtip',
                        searching: false, 
                        paging: false, 
                        info: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                className: 'buttons-excel',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                    // Add the title, student name, and student ID to the Excel document
                                    var title = 'Contrast Returned and Pending Returns Data';
                                    
                                    // Add title, student name, and student ID to separate rows
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr></table>';
                                },
                                filename: 'Contrast Returned and Pending Returns Data' // Set the filename for download
                            },
                            {
                                extend: 'csvHtml5',
                                className: 'buttons-csv',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (csv) {
                                    // Add the title and student information to the CSV content
                                    var csvContent = 'Contrast Returned and Pending Returns Data\n';

                                    csvContent += csv;

                                    return csvContent;
                                },
                                filename: 'Contrast Returned and Pending Returns Data' // Set the filename for download
                            },
                            {
                                extend: 'pdfHtml5',
                                className: 'buttons-pdf',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (doc) {
                                    // Remove the title
                                    doc.content.splice(0, 1);

                                    // Convert Chart.js chart to a base64-encoded PNG image
                                    var canvas = document.createElement('canvas');
                                    canvas.width = 300; // Adjust width as needed
                                    canvas.height = 300; // Adjust height as needed
                                    var ctx = canvas.getContext('2d');
                                    ctx.drawImage(document.getElementById('borrowReturnChartContainer'), 0, 0, canvas.width, canvas.height);
                                    var chartImage = canvas.toDataURL('image/png');

                                    doc.content.unshift({
                                        margin: [0, 0, 0, 12],
                                        alignment: 'center',
                                        image: chartImage,
                                    });

                                    doc.content.unshift({
                                        margin: [100, 0, 0, -50],
                                        alignment: 'center',
                                        image: resizedImage,
                                    });

                                    // Add the title and student information
                                    doc.content.splice(1, 0, {
                                        text: [
                                            { text: 'Contrast Returned and Pending Returns Data\n', fontSize: 14, bold: true },
                                        ],
                                        alignment: 'left',
                                        margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                                    });
                                },
                                filename: 'Contrast Returned and Pending Returns Data' // Set the filename for download
                            }
                        ],
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                    });

                    allTransactionHistory = $('.all-transaction-history-table').DataTable({
                        dom: 'lBfrtip',
                        searching: false, 
                        paging: false, 
                        info: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                className: 'buttons-excel',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                    // Add the title, student name, and student ID to the Excel document
                                    var title = 'All Book Transactions by Publish Year';
                                    
                                    // Add title, student name, and student ID to separate rows
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr></table>';
                                },
                                filename: 'All Book Transactions by Publish Year' // Set the filename for download
                            },
                            {
                                extend: 'csvHtml5',
                                className: 'buttons-csv',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (csv) {
                                    // Add the title and student information to the CSV content
                                    var csvContent = 'All Book Transactions by Publish Year\n';

                                    csvContent += csv;

                                    return csvContent;
                                },
                                filename: 'All Book Transactions by Publish Year' // Set the filename for download
                            },
                            {
                                extend: 'pdfHtml5',
                                className: 'buttons-pdf',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (doc) {
                                    // Remove the title
                                    doc.content.splice(0, 1);

                                    // Convert Chart.js chart to a base64-encoded PNG image
                                    var canvas = document.createElement('canvas');
                                    canvas.width = 300; // Adjust width as needed
                                    canvas.height = 300; // Adjust height as needed
                                    var ctx = canvas.getContext('2d');
                                    ctx.drawImage(document.getElementById('bookTransactionsChart'), 0, 0, canvas.width, canvas.height);
                                    var chartImage = canvas.toDataURL('image/png');

                                    doc.content.unshift({
                                        margin: [0, 0, 0, 12],
                                        alignment: 'center',
                                        image: chartImage,
                                    });

                                    doc.content.unshift({
                                        margin: [100, 0, 0, -50],
                                        alignment: 'center',
                                        image: resizedImage,
                                    });

                                    // Add the title and student information
                                    doc.content.splice(1, 0, {
                                        text: [
                                            { text: 'All Book Transactions by Publish Year\n', fontSize: 14, bold: true },
                                        ],
                                        alignment: 'left',
                                        margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                                    });
                                },
                                filename: 'All Book Transactions by Publish Year' // Set the filename for download
                            }
                        ],
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                    });

                    rankingPerTotalTransaction = $('.ranking-of-book-publish-year-by-total-transaction').DataTable({
                        dom: 'lBfrtip',
                        searching: false, 
                        paging: false, 
                        info: false,
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                className: 'buttons-excel',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (xlsx) {
                                    var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                    // Add the title, student name, and student ID to the Excel document
                                    var title = 'Rankings of Book Publish Years by Total Transactions';
                                    
                                    // Add title, student name, and student ID to separate rows
                                    sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr></table>';
                                },
                                filename: 'Rankings of Book Publish Years by Total Transactions' // Set the filename for download
                            },
                            {
                                extend: 'csvHtml5',
                                className: 'buttons-csv',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (csv) {
                                    // Add the title and student information to the CSV content
                                    var csvContent = 'Rankings of Book Publish Years by Total Transactions\n';

                                    csvContent += csv;

                                    return csvContent;
                                },
                                filename: 'Rankings of Book Publish Years by Total Transactions' // Set the filename for download
                            },
                            {
                                extend: 'pdfHtml5',
                                className: 'buttons-pdf',
                                init: function (api, node, config) {
                                    $(node).hide();
                                },
                                customize: function (doc) {
                                    // Remove the title
                                    doc.content.splice(0, 1);

                                    doc.content.unshift({
                                        margin: [100, 0, 0, -50],
                                        alignment: 'center',
                                        image: resizedImage,
                                    });

                                    // Add the title and student information
                                    doc.content.splice(1, 0, {
                                        text: [
                                            { text: 'Rankings of Book Publish Years by Total Transactions\n', fontSize: 14, bold: true },
                                        ],
                                        alignment: 'left',
                                        margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                                    });
                                },
                                filename: 'Rankings of Book Publish Years by Total Transactions' // Set the filename for download
                            }
                        ],
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
                    });

                    // Trigger Excel export
                    $('#excel-btn').click(function() {
                        table.buttons('.buttons-excel').trigger();
                        <?php
                            if ($_GET['pending_book_returns'] != 0) {
                        ?>
                            pendingBooksTable.buttons('.buttons-excel').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['books_borrowed_and_returned'] != 0) {
                        ?>
                            bookBorrowedAndReturnTable.buttons('.buttons-excel').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['contrast_books_returned_and_pending_returns'] != 0) {
                        ?>
                            contrastBooksReturnedAndPendingReturns.buttons('.buttons-excel').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['all_transaction_history'] != 0) {
                        ?>
                            allTransactionHistory.buttons('.buttons-excel').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['rankings_per_total_transaction'] != 0) {
                        ?>
                            rankingPerTotalTransaction.buttons('.buttons-excel').trigger();
                        <?php 
                            }
                        ?>
                    });

                    // Trigger CSV export
                    $('#csv-btn').click(function() {
                        table.buttons('.buttons-csv').trigger();
                        <?php
                            if ($_GET['pending_book_returns'] != 0) {
                        ?>
                            pendingBooksTable.buttons('.buttons-csv').trigger();
                        <?php 
                            }
                        ?>

                        <?php
                            if ($_GET['books_borrowed_and_returned'] != 0) {
                        ?>
                            bookBorrowedAndReturnTable.buttons('.buttons-csv').trigger();
                        <?php 
                            }
                        ?>

                        <?php
                            if ($_GET['contrast_books_returned_and_pending_returns'] != 0) {
                        ?>
                            contrastBooksReturnedAndPendingReturns.buttons('.buttons-csv').trigger();
                        <?php 
                            }
                        ?>

                        <?php
                            if ($_GET['all_transaction_history'] != 0) {
                        ?>
                            allTransactionHistory.buttons('.buttons-csv').trigger();
                        <?php 
                            }
                        ?>

                        <?php
                            if ($_GET['rankings_per_total_transaction'] != 0) {
                        ?>
                            rankingPerTotalTransaction.buttons('.buttons-csv').trigger();
                        <?php 
                            }
                        ?>
                    });

                    // Trigger PDF export
                    $('#pdf-btn').click(function() {
                        table.buttons('.buttons-pdf').trigger();
                        <?php
                            if ($_GET['pending_book_returns'] != 0) {
                        ?>
                            pendingBooksTable.buttons('.buttons-pdf').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['books_borrowed_and_returned'] != 0) {
                        ?>
                            bookBorrowedAndReturnTable.buttons('.buttons-pdf').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['contrast_books_returned_and_pending_returns'] != 0) {
                        ?>
                            contrastBooksReturnedAndPendingReturns.buttons('.buttons-pdf').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['all_transaction_history'] != 0) {
                        ?>
                            allTransactionHistory.buttons('.buttons-pdf').trigger();
                        <?php 
                            }
                        ?>
                        <?php
                            if ($_GET['rankings_per_total_transaction'] != 0) {
                        ?>
                            rankingPerTotalTransaction.buttons('.buttons-pdf').trigger();
                        <?php 
                            }
                        ?>
                    });
                };
                image.src = dataURL;
            })
            .catch(function(error) {
                console.error(error);
            });
    });
</script>
