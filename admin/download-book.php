<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>

<body class="hold-transition skin-maroon-gold sidebar-mini">
    <div class="wrapper">
        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content">
                <?php
                    if (isset($_SESSION['error']) && is_array($_SESSION['error']) && !empty($_SESSION['error'])) { ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-warning"></i> Error!</h4>
                        <ul>
                            <?php foreach ($_SESSION['error'] as $error) {
                                echo "<li>" . $error . "</li>";
                            } ?>
                        </ul>
                    </div>
                <?php
                    unset($_SESSION['error']);
                }

                if (isset($_SESSION['success'])) { ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-check"></i> Success!</h4>
                        <?php echo $_SESSION['success']; ?>
                    </div>
                <?php
                    unset($_SESSION['success']);
                } ?>

<div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <!-- <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Borrow</a> -->
              <!-- <button id="downloadButton" class="btn btn-primary btn-sm btn-flat"> Download</button> -->
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
                    <div class="row pending-book-returns">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-body">
                                    <div class="row">
                                    <div class="col-md-12">
                                        <!-- <div id="chartContainer" style="height: 300px; width: 25%; margin-left: auto; margin-right: auto;"></div> -->
                                        <canvas id="chartContainer" style="height: 250px; margin-left: auto; margin-right: auto;"></canvas>
                                    </div>
                                </div>
                                <?php
                                    // Fetch data for pie chart
                                    $publishQuery = "SELECT COUNT(*) AS count, borrow.date_borrow
                                                    FROM borrow
                                                    LEFT JOIN books ON borrow.book_id = books.id";
                                
                                    $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                                    $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

                                    // Add the date range condition
                                    if ($startDate && $endDate) {
                                        $publishQuery .= " WHERE borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                    }
                                    $publishQuery .= " AND borrow.status = 0";
                                    $publishQuery .= " GROUP BY date_borrow";
                                    $publishResult = $conn->query($publishQuery);

                                    // Fetch data for each year and create tables for pending book returns
                                    $totalTransactions = 0;
                                    while ($publishRow = $publishResult->fetch_assoc()) {
                                        $year = $publishRow['date_borrow'];
                                        $count = $publishRow['count'];
                                        $totalTransactions += $count;

                                        // Output table for pending book returns for each year
                                        echo "<div class='row'>";
                                        echo "<div class='col-xs-12'>";
                                        echo "<div class='box'>";
                                        echo "<div class='box-header with-border'>";
                                        echo "<h4><b>Pending Book Returns Data for $year</b></h4>";
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
                                        // Fetch data for the specific year for pending book returns
                                        $yearQuery = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                                        FROM borrow b
                                        LEFT JOIN returns r ON b.book_id = r.book_id AND r.student_id = b.student_id
                                        LEFT JOIN students ON students.id = b.student_id
                                        LEFT JOIN books ON books.id = b.book_id
                                        WHERE b.date_borrow = '{$publishRow['date_borrow']}'"; // Wrap date_borrow in single quotes

                                        // Add the date range condition
                                        if ($startDate && $endDate) {
                                        $yearQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                        }

                                        $yearQuery .= " AND b.status = 0"; // Move the status condition here

                                        $yearQuery .= " ORDER BY b.date_borrow DESC";

                                        $yearResult = $conn->query($yearQuery);


                                        if ($yearResult->num_rows > 0) {
                                            while ($row = $yearResult->fetch_assoc()) {
                                                $status = ($row['status']) ? '<span class="label label-success">returned</span>' : '<span class="label label-danger">not returned</span>';
                                                $returnDate = ($row['status'] == 0) ? "Not Returned Yet" : date('M d, Y', strtotime($row['return_date']));
                                                echo "<tr>";
                                                echo "<td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>";
                                                echo "<td>" . $returnDate . "</td>";
                                                echo "<td>" . $row['stud'] . "</td>";
                                                echo "<td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>";
                                                echo "<td>" . $row['isbn'] . "</td>";
                                                echo "<td>" . $row['title'] . "</td>";
                                                echo "<td>" . $status . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7'>No Pending Book Returns for Books Published in $year</td></tr>";
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
                <?php 
                    }
                ?>
                            </div>
                        </div>
                    </div>
                <?php
                    if ($_GET['books_borrowed_and_returned'] != 0) {
                ?>
                    <div class="row books-borrowed-and-returned">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <canvas id="returnedChartContainer" style="height: 250px; margin-left: auto; margin-right: auto;"></canvas>
                                        </div>
                                    </div>
                                    <?php
                                       
                                        $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                                        $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

                                        // Fetch data for book borrowed and returned
                                        $returnedQuery = "SELECT DISTINCT b.id, b.*, books.isbn, books.title, books.author, COUNT(*) AS count
                                        FROM borrow b
                                        LEFT JOIN books ON books.id = b.book_id
                                        WHERE b.status = 1";


                                        // Add the date range condition
                                        if ($startDate && $endDate) {
                                            $returnedQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                        }

                                        $returnedQuery .= " AND b.status = 1";
                                        $returnedQuery .= " GROUP BY date_borrow"; // Corrected grouping
                                        $returnedResult = $conn->query($returnedQuery);

                                        // Fetch data for each year and create tables for book borrowed and returned
                                        $totalTransactionsReturned = 0;
                                       
                                        while ($returnedRow = $returnedResult->fetch_assoc()) {
                                            $year = $returnedRow['date_borrow'];
                                            $count = $returnedRow['count'];
                                            $totalTransactionsReturned += $count;

                                            // Output table for book borrowed and returned for each year
                                            echo "<div class='row'>";
                                            echo "<div class='col-xs-12'>";
                                            echo "<div class='box'>";
                                            echo "<div class='box-header with-border'>";
                                            echo "<h4><b>Book Borrowed and Returned Data for $year</b></h4>";
                                            echo "</div>";
                                            echo "<div class='box-body'>";
                                            echo "<table class='table table-bordered book-borrowed-and-return-table'>";
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
                                            $returnedYearQuery = "SELECT DISTINCT r.id, r.*, r.date_return AS return_date, b.date_borrow, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                                            FROM returns r
                                            LEFT JOIN students ON students.id = r.student_id 
                                            LEFT JOIN books ON books.id = r.book_id 
                                            LEFT JOIN borrow b ON b.book_id = r.book_id AND b.student_id = r.student_id 
                                            WHERE b.date_borrow = '{$returnedRow['date_borrow']}'
                                            AND b.status = 1";

                                            // Add the date range condition
                                            if ($startDate && $endDate) {
                                                $returnedYearQuery .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                            }

                                            $returnedYearQuery .= " AND b.status =1";
                                            $returnedYearQuery .= " ORDER BY b.date_borrow DESC";

                                            $returnedYearResult = $conn->query($returnedYearQuery);

                                            if ($returnedYearResult && $returnedYearResult->num_rows > 0) {
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
                                    <br>
                                    <?php
                                        // Report data analytics
                                        $borrowPercentage = ($totalTransactionsReturned > 0) ? (($totalTransactions / $totalTransactionsReturned) * 100) : 0;
                                        echo "<p>The total borrow transactions are: $totalTransactions, while the total book borrowed and returned transactions are: $totalTransactionsReturned.</p>";
                                        echo "<p>In the selected date range, all the total book borrowing transactions are $totalTransactions. This constitutes a borrow percentage of $borrowPercentage%.</p>";
                                    ?>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>  
                <?php
                    }
                ?>

                <?php
                    if ($_GET['contrast_books_returned_and_pending_returns'] != 0) {
                ?>
                    <div class="row contrast-books-returned-and-pending-returns">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Contrast Returned and Pending Returns Data</h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <canvas id="borrowReturnChartContainer" style="height: 250px; margin-left: auto; margin-right: auto;"></canvas>
                                            <table class='table table-bordered contrast-returned-and-pending-returns-table' style="display: none;">
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
                                        <h3 class="box-title">Rankings of Book Transaction Dates</h3>
                                    </div>
                                    <div class="row">
                                        <div class="box-body ranking-of-book-publish-year-by-total-transaction-table">
                                        <div class="col-md-12">
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
                        </div>
                    <?php 
                        }
                    ?>
            </section>
        </div>
        <?php include 'includes/footer.php'; ?>
        <?php include 'includes/borrow_modal.php'; ?>
        <?php include 'includes/scripts.php'; ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
        <script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script type="text/javascript" language="javascript">
            $(document).ready(function () {
                // // Function to convert image file to base64 data URL
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
                
                var imagePath = '../images/logos/libguard-logo-header2.png';
                var bannerWidth = 200; 
                var bannerHeight = 100;
                var table;

                //Datatable variables
                var pendingBooksTable;
                var bookBorrowedAndReturnTable;
                var contrastBooksReturnedAndPendingReturns;
                var allTransactionHistory
                var rankingPerTotalTransaction;

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

                            // Initialize Pending DataTable
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

                            // Initialize Book Borrowed and Return DataTable
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

                // // AJAX call on clicking PDF button
                // $('#pdf-btn').click(function(){
                //     exportData('pdf');
                // });

                // // AJAX call on clicking CSV button
                // $('#csv-btn').click(function(){
                //     exportData('csv');
                // });

                // // AJAX call on clicking Excel button
                // $('#excel-btn').click(function(){
                //     exportData('excel');
                // });

                // // Function to perform AJAX call based on format
                // function exportData(format){
                //     $.ajax({
                //         type: 'POST',
                //         url: 'generate-pdf/publish_report_pdf.php',
                //         data: { format: format },
                //         success: function(response){
                //             // Handle response, e.g., show success message or download file
                //             alert(response);
                //         },
                //         error: function(xhr, status, error){
                //             // Handle error
                //             console.error(xhr.responseText);
                //         }
                //     });
                // }
            });
        </script>



        <?php
            if ($_GET['pending_book_returns'] != 0) {
        ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    <?php
                        // Fetch data for pie chart
                        $pieChartData = array();
                        $pieChartQuery = "SELECT COUNT(*) AS count, borrow.date_borrow
                                            FROM borrow
                                            LEFT JOIN books ON borrow.book_id = books.id
                                            WHERE borrow.status = 0";

                        // Add the date range condition
                        if ($startDate && $endDate) {
                            $pieChartQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                        }
                        $pieChartQuery .= " AND borrow.status = 0";
                        $pieChartQuery .= " GROUP BY date_borrow";
                        $pieChartResult = $conn->query($pieChartQuery);

                        while ($pieChartRow = $pieChartResult->fetch_assoc()) {
                            $pieChartData[] = array(
                                "date_borrow" => $pieChartRow['date_borrow'],
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
                    const pendingBookReturnsPublishYear = pendingBookReturns.map(element => element.date_borrow + ' : ' + element.count);
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
                                            size: 13,
                                            weight: 'bolder'
                                        }
                                    },
                                    padding: {
                    top: 40 // Increase the top padding of the legend
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
                        
                        $booksBorrowedAndReturnedQuery = "SELECT COUNT(*) AS count, borrow.date_borrow
                        FROM borrow
                        LEFT JOIN books ON borrow.book_id = books.id
                        WHERE borrow.status = 1";

                        // Add the date range condition
                        if ($startDate && $endDate) {
                            $booksBorrowedAndReturnedQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                        }
                        
                        $booksBorrowedAndReturnedQuery .= " AND borrow.status = 1";
                        $booksBorrowedAndReturnedQuery .= " GROUP BY date_borrow";
                        $booksBorrowedAndReturnedResult = $conn->query($booksBorrowedAndReturnedQuery);

                        while ($returnedRow = $booksBorrowedAndReturnedResult->fetch_assoc()) {
                            $year = $returnedRow['date_borrow'];
                            $count = $returnedRow['count'];
                            $returnedChartData[] = array('date_borrow' => $year, 'count' => $count);
                        }

                        // Generate random background colors
                        $backgroundColor = [];
                        for ($i = 0; $i < count($returnedChartData); $i++) {
                            $backgroundColor[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                        }
                    ?>

                    const bookBorrowedAndReturned = <?php echo json_encode($returnedChartData); ?>;
                    const bookBorrowedAndReturnedPublishYear = bookBorrowedAndReturned.map(element => element.date_borrow + ' : ' + element.count);
                    const bookBorrowedAndReturnedCount = bookBorrowedAndReturned.map(element => element.count);
                    const bookBorrowedAndReturnedBackgroundColor = <?php echo json_encode($backgroundColor); ?>;
                    const bookBorrowedAndReturnedContainer = document.getElementById('returnedChartContainer');

                    new Chart(bookBorrowedAndReturnedContainer, {
                        type: 'pie',
                        animationEnabled: true,
                        data: {
                            labels: bookBorrowedAndReturnedPublishYear,
                            datasets: [{
                                label: 'Books Borrowed and Returned',
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
                                    text: 'Books Borrowed and Returned',
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
        if ($_GET['contrast_books_returned_and_pending_returns'] != 0) {
    ?>
        <script>
            window.onload = function () {
                var borrowReturnData = [
                { label: "Pending Book Returns", y: <?php echo $totalTransactions; ?> },
                { label: "Book Borrowed and Returned", y: <?php echo $totalTransactionsReturned; ?> }
            ];

            var borrowReturnChart = new Chart(document.getElementById('borrowReturnChartContainer'), {
    type: 'pie',
    data: {
        labels: ["Pending Book Returns", "Book Borrowed and Returned"],
        datasets: [{
            data: [<?php echo $totalTransactions; ?>, <?php echo $totalTransactionsReturned; ?>],
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
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: {
                        size: 13,
                        weight: 'bolder'
                    }
                }
            },
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
            }
        }
    }
});

        }
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
                $query = "SELECT COUNT(*) AS total_transactions, borrow.*
                            FROM borrow
                            LEFT JOIN books ON borrow.book_id = books.id
                            LEFT JOIN category ON books.category_id = category.id";
                
                        // Add the date range condition
                        if ($startDate && $endDate) {
                            $query .= " WHERE borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                        }

                        $query .= " GROUP BY date_borrow";
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
                })
            });
        </script>
    <?php
        }
    ?>

</body>

</html>
