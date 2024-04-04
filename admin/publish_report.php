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

                <div class="box-header with-border">
                    <button id="downloadButton" class="btn btn-primary btn-sm btn-flat"> Download</button>
                </div>

                <?php
                    if ($_GET['pending_book_returns'] != 0) {
                ?>
                    <div class="row pending-book-returns">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- <div id="chartContainer" style="height: 300px; width: 25%; margin-left: auto; margin-right: auto;"></div> -->
                                        <canvas id="chartContainer" style="height: 120px; margin-left: auto; margin-right: auto;"></canvas>
                                    </div>
                                </div>
                                <?php
                                    // Fetch data for pie chart
                                    $publishQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                                    FROM borrow
                                                    LEFT JOIN books ON borrow.book_id = books.id";
                                    $selected_publish = isset($_GET['selected_publish']) ? $_GET['selected_publish'] : null;
                                    $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                                    $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

                                    if ($selected_publish !== null) {
                                        $publishYears = explode(',', $selected_publish);
                                        $publishYears = array_map('intval', $publishYears);
                                        $publishYearsString = implode(',', $publishYears);

                                        $publishQuery .= " WHERE YEAR(books.publish_date) IN ($publishYearsString)";
                                    }

                                    // Add the date range condition
                                    if ($startDate && $endDate) {
                                        $publishQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                    }
                                    $publishQuery .= " AND borrow.status = 0";
                                    $publishQuery .= " GROUP BY publish_year";
                                    $publishResult = $conn->query($publishQuery);

                                    // Fetch data for each year and create tables for pending book returns
                                    $totalTransactions = 0;
                                    while ($publishRow = $publishResult->fetch_assoc()) {
                                        $year = $publishRow['publish_year'];
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
                                        echo "<table class='table table-bordered'>";
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
                                        $yearQuery = "SELECT borrow.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                                                    FROM borrow
                                                    LEFT JOIN returns r ON borrow.book_id = r.book_id
                                                    LEFT JOIN students ON students.id = borrow.student_id
                                                    LEFT JOIN books ON books.id = borrow.book_id
                                                    WHERE YEAR(books.publish_date) = $year AND (borrow.status = 0 OR r.date_return IS NULL)";

                                        // Add the date range condition
                                        if ($startDate && $endDate) {
                                            $yearQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                        }

                                        $yearQuery .= " ORDER BY borrow.date_borrow DESC";

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
                <?php 
                    }
                ?>

                <?php
                    if ($_GET['books_borrowed_and_returned'] != 0) {
                ?>
                    <div class="row books-borrowed-and-returned">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <canvas id="returnedChartContainer" style="height: 120px; margin-left: auto; margin-right: auto;"></canvas>
                                        </div>
                                    </div>
                                    <?php
                                        $selected_publish = isset($_GET['selected_publish']) ? $_GET['selected_publish'] : null;
                                        $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                                        $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

                                        // Fetch data for book borrowed and returned
                                        $returnedQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                                        FROM borrow
                                                        INNER JOIN returns ON borrow.book_id = returns.book_id
                                                        LEFT JOIN books ON borrow.book_id = books.id";

                                        if ($selected_publish !== null) {
                                            $publishYears = explode(',', $selected_publish);
                                            $publishYearsString = implode(',', $publishYears);
                                            $returnedQuery .= " WHERE YEAR(books.publish_date) IN ($publishYearsString)";
                                        }

                                        // Add the date range condition
                                        if ($startDate && $endDate) {
                                            $returnedQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                        }

                                        $returnedQuery .= " GROUP BY publish_year";
                                        $returnedResult = $conn->query($returnedQuery);

                                        // Fetch data for each year and create tables for book borrowed and returned
                                        $totalTransactionsReturned = 0;
                                        $totalTransactions = 0;
                                        while ($returnedRow = $returnedResult->fetch_assoc()) {
                                            $year = $returnedRow['publish_year'];
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
                                            $returnedYearQuery = "SELECT borrow.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                                                                FROM borrow
                                                                INNER JOIN returns r ON borrow.book_id = r.book_id
                                                                LEFT JOIN students ON students.id = borrow.student_id
                                                                LEFT JOIN books ON books.id = borrow.book_id
                                                                WHERE YEAR(books.publish_date) = $year";

                                            // Add the date range condition
                                            if ($startDate && $endDate) {
                                                $returnedYearQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                            }

                                            $returnedYearQuery .= " ORDER BY borrow.date_borrow DESC";

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
                                            <div id="borrowReturnChartContainer" style="height: 300px; width: 25%; margin-left: auto; margin-right: auto;"></div>
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
                    <div class="row all-transaction-history">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">All Book Transactions by Publish Year</h3>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <canvas id="bookTransactionsChart" style="height: 300px; width: 25%; margin-left: auto; margin-right: auto;"></canvas>
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
                    <div class="row ranking-per-total-transaction">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Rankings of Book Publish Years by Total Transactions</h3>
                                </div>
                                <div class="box-body">
                                    <ul class="list-group">
                                        <?php
                                            $selected_publish = isset($_GET['selected_publish']) ? $_GET['selected_publish'] : null;
                                            $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                                            $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;
                                            // Fetch data for all borrowers grouped by publish year
                                            $borrowersQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS total_borrowers
                                                                FROM borrow
                                                                LEFT JOIN books ON borrow.book_id = books.id
                                                                WHERE borrow.status IN (0, 1)"; // Include both returned and not returned
                                            if ($startDate && $endDate) {
                                                $borrowersQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                                            }
                                            $borrowersQuery .= " GROUP BY publish_year";
                                            $borrowersResult = $conn->query($borrowersQuery);

                                            // Prepare data for CanvasJS
                                            $barChartData = array();
                                            $allYears = array(); // Store all years to check against selected publish years
                                            while ($borrowersRow = $borrowersResult->fetch_assoc()) {
                                                $year = $borrowersRow['publish_year'];
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
                                                    $yearsWithTransactions[] = "Year $year - Total Borrowers: $transactions";
                                                } else {
                                                    $yearsWithNoTransactions[] = $year;
                                                }
                                            }

                                            // Output years with transactions
                                            foreach ($yearsWithTransactions as $yearData) {
                                                echo "<li class='list-group-item'>TOP $rank: $yearData</li>";
                                                $rank++;
                                            }

                                            // Output combined years with 0 transactions
                                            if (!empty($yearsWithNoTransactions)) {
                                                $combinedYears = implode(', ', $yearsWithNoTransactions);
                                                echo "<li class='list-group-item'>Years with 0 transactions: $combinedYears</li>";
                                            }
                                        ?>
                                    </ul>
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
        <script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#downloadButton').click(function (e) {
                    e.preventDefault();
                    console.log("Download button clicked"); // Debug statement
                    var contentWrapper = document.querySelector(".content-wrapper");

                    var classesToCheck = [".pending-book-returns", ".books-borrowed-and-returned", ".contrast-books-returned-and-pending-returns", ".all-transaction-history", ".ranking-per-total-transaction"];

                    var { jsPDF } = window.jspdf;
                    var pdf = new jsPDF('p', 'mm', 'a4', 'portrait');

                    var headerHtml = `
                        <div class="image-container row">
                            <div class="col-md-12">
                                <img src="../images/libguard-logo.png" style="width: 100%; margin-left: auto; margin-right: auto;">
                            </div>
                        </div>
                        <hr style="border: 1px solid #800000; margin-top: 10px;">
                    `;

                    var footerHtml = `
                        <hr style="border: 1px solid #800000; margin-top: 10px;">
                    `;

                    function addHeaderFooterToPdf(pageIndex, totalPages) {
                        var contentHeight = pdf.internal.pageSize.height - 20;
                        var headerHeight = 50;
                        var footerHeight = 30;

                        pdf.setPage(pageIndex);
                        pdf.html(headerHtml, {
                            x: 10,
                            y: 10,
                            width: pdf.internal.pageSize.width - 20,
                            html2canvas: {
                                scale: 10
                            }
                        });

                        pdf.setPage(pageIndex);
                        pdf.html(footerHtml, {
                            x: 10,
                            y: pdf.internal.pageSize.height - footerHeight,
                            width: pdf.internal.pageSize.width - 20,
                            html2canvas: {
                                scale: 10
                            }
                        });
                    }

                    function addElementToPdf(element, isFirstElement) {
                        return new Promise((resolve, reject) => {
                            html2canvas(element).then(canvas => {
                                if (!isFirstElement) {
                                    pdf.addPage();
                                }

                                pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, pdf.internal.pageSize.width, pdf.internal.pageSize.height);

                                resolve();
                            }).catch(error => reject(error));
                        });
                    }

                    var promises = [];
                    var isFirstElement = true;

                    classesToCheck.forEach(function(currClass) {
                        var elements = contentWrapper.querySelectorAll(currClass);
                        elements.forEach(function(element) {
                            if (element.offsetWidth > 0 || element.offsetHeight > 0) {
                                promises.push(addElementToPdf(element, isFirstElement));
                                isFirstElement = false;
                            }
                        });
                    });

                    Promise.all(promises)
                        .then(() => {
                            for (var i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
                                addHeaderFooterToPdf(i, pdf.internal.getNumberOfPages());
                            }

                            pdf.save('book_transactions.pdf');
                        })
                        .catch(error => {
                            console.error("Error generating PDF:", error);
                        });
                });

            });
        </script>

        //! NO EXISTING ID FOR GRAPH, MUST REMOVE
        <script type="text/javascript">
            window.onload = function () {
                <?php
                // Fetch data for all borrowers grouped by publish year
                $borrowersQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS total_borrowers
                                        FROM borrow
                                        LEFT JOIN books ON borrow.book_id = books.id
                                        WHERE borrow.status IN (0, 1)"; // Include both returned and not returned
                if ($startDate && $endDate) {
                    $borrowersQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                }
                $borrowersQuery .= " GROUP BY publish_year";
                $borrowersResult = $conn->query($borrowersQuery);

                // Prepare data for CanvasJS
                $barChartData = array();
                while ($borrowersRow = $borrowersResult->fetch_assoc()) {
                    $year = $borrowersRow['publish_year'];
                    $totalBorrowers = $borrowersRow['total_borrowers'];
                    $barChartData[] = array(
                        "label" => $year,
                        "y" => $totalBorrowers
                    );
                }
                ?>
                var barChart = new CanvasJS.Chart("barChartContainer", {
                    responsive: true,
                    animationEnabled: true,
                    title: {
                        text: "Total Borrowers by Publish Year"
                    },
                    axisX: {
                        title: "Publish Year"
                    },
                    axisY: {
                        title: "Total Borrowers"
                    },
                    data: [{
                        type: "column",
                        color: "red",
                        dataPoints: <?php echo json_encode($barChartData, JSON_NUMERIC_CHECK); ?>
                    }]
                });
                barChart.render();
            }
        </script>


        <?php
            if ($_GET['pending_book_returns'] != 0) {
        ?>
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', function() {
                    <?php
                        // Fetch data for pie chart
                        $pieChartData = array();
                        $pieChartQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                            FROM borrow
                                            LEFT JOIN books ON borrow.book_id = books.id";

                        if ($selected_publish !== null) {
                            $publishYears = explode(',', $selected_publish);
                            $publishYears = array_map('intval', $publishYears);
                            $publishYearsString = implode(',', $publishYears);

                            $pieChartQuery .= " WHERE YEAR(books.publish_date) IN ($publishYearsString)";
                        }

                        // Add the date range condition
                        if ($startDate && $endDate) {
                            $pieChartQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                        }
                        $pieChartQuery .= " AND borrow.status = 0";
                        $pieChartQuery .= " GROUP BY publish_year";
                        $pieChartResult = $conn->query($pieChartQuery);

                        while ($pieChartRow = $pieChartResult->fetch_assoc()) {
                            $pieChartData[] = array(
                                "publish_year" => $pieChartRow['publish_year'],
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
                    const pendingBookReturnsPublishYear = pendingBookReturns.map(element => element.publish_year);
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
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Pending Book Returns by Year Published of Books'
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
                        $booksBorrowedAndReturnedQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                        FROM borrow
                                        INNER JOIN returns ON borrow.book_id = returns.book_id
                                        LEFT JOIN books ON borrow.book_id = books.id";

                        if ($selected_publish !== null) {
                            $publishYears = explode(',', $selected_publish);
                            $publishYearsString = implode(',', $publishYears);
                            $booksBorrowedAndReturnedQuery .= " WHERE YEAR(books.publish_date) IN ($publishYearsString)";
                        }

                        // Add the date range condition
                        if ($startDate && $endDate) {
                            $booksBorrowedAndReturnedQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
                        }

                        $booksBorrowedAndReturnedQuery .= " GROUP BY publish_year";
                        $booksBorrowedAndReturnedResult = $conn->query($booksBorrowedAndReturnedQuery);

                        while ($returnedRow = $booksBorrowedAndReturnedResult->fetch_assoc()) {
                            $year = $returnedRow['publish_year'];
                            $count = $returnedRow['count'];
                            $returnedChartData[] = array('publish_year' => $year, 'count' => $count);
                        }

                        // Generate random background colors
                        $backgroundColor = [];
                        for ($i = 0; $i < count($returnedChartData); $i++) {
                            $backgroundColor[] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                        }
                    ?>

                    const bookBorrowedAndReturned = <?php echo json_encode($returnedChartData); ?>;
                    const bookBorrowedAndReturnedPublishYear = bookBorrowedAndReturned.map(element => element.publish_year);
                    const bookBorrowedAndReturnedCount = bookBorrowedAndReturned.map(element => element.count);
                    const bookBorrowedAndReturnedBackgroundColor = <?php echo json_encode($backgroundColor); ?>;
                    const bookBorrowedAndReturnedContainer = document.getElementById('returnedChartContainer');

                    new Chart(bookBorrowedAndReturnedContainer, {
                        type: 'pie',
                        data: {
                            labels: bookBorrowedAndReturnedPublishYear,
                            datasets: [{
                                label: 'Book Borrowed and Returned',
                                data: bookBorrowedAndReturnedCount,
                                backgroundColor: bookBorrowedAndReturnedBackgroundColor,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Book Borrowed and Returned by Year Published of Books'
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
            //! FIX ME
            <script type="text/javascript">

                <?php
                    $totalTransactionData = $totalTransactions;
                    $totalTransactionsReturnedData = $totalTransactionsReturned;
                ?>
                
                const pendingBookReturns = <?php echo json_encode($totalTransactions); ?>;
                const bookBorrowedAndReturn = <?php echo json_encode($totalTransactionsReturned); ?>
               
                const pendingBookReturnsAndBookBorrowedAndReturnedContainer = document.getElementById('borrowReturnChartContainer');

                new Chart(pendingBookReturnsAndBookBorrowedAndReturnedContainer, {
                    type: 'pie',
                    data: {
                        labels: 'Pending Book Returns vs Book Borrowed and Returned',
                        datasets: [{
                            label: [
                                'Pending Book Returns',
                                'Book Borrowed and Returned'
                            ],
                            data: [
                                pendingBookReturns,
                                bookBorrowedAndReturn
                            ],
                            backgroundColor: ['blue', 'red'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Pending Book Returns vs Book Borrowed and Returned'
                            }
                        }
                    },
                });


                // window.onload = function () {
                //     // Pie chart for borrow and return transactions
                //     var borrowReturnData = [
                //         { label: "Pending Book Returns", y: <?php echo $totalTransactions; ?> },
                //         { label: "Book Borrowed and Returned", y: <?php echo $totalTransactionsReturned; ?> }
                //     ];

                //     var borrowReturnChart = new CanvasJS.Chart("borrowReturnChartContainer", {
                //         responsive: true,
                //         animationEnabled: true,
                //         title: {
                //             text: "Pending Book Returns vs Book Borrowed and Returned"
                //         },
                //         legend: {
                //             itemWidth: 120
                //         },
                //         data: [{
                //             type: "pie",
                //             showInLegend: true,
                //             legendText: "{label}: {y}",
                //             startAngle: 0,
                //             yValueFormatString: "##0",
                //             indexLabel: "{label} {y}",
                //             dataPoints: borrowReturnData
                //         }]
                //     });
                //     borrowReturnChart.render();
                // }
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
                    $.ajax({
                        url: 'fetch_book_transactions.php', // Path to your PHP script to fetch data
                        method: 'GET',
                        success: function (data) {
                            var years = [];
                            var transactions = [];

                            for (var i in data) {
                                years.push(data[i].publish_year);
                                transactions.push(data[i].total_transactions);
                            }

                            var ctx = document.getElementById('bookTransactionsChart').getContext('2d');
                            var chart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: years,
                                    datasets: [{
                                        label: 'Total Transactions',
                                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        borderWidth: 1,
                                        data: transactions
                                    }]
                                },
                            });
                        },
                        error: function (data) {
                            console.log(data);
                        }
                    });
                });
            </script>
        <?php
            }
        ?>
</body>

</html>
