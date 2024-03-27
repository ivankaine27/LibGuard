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
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                          
                            <div class="box-body">
                                <div id="chartContainer" style="height: 300px; width: 100%;"></div>
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
                                    $yearQuery = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                                                FROM borrow b
                                                LEFT JOIN returns r ON borrow.book_id = r.book_id
                                                LEFT JOIN students ON students.id = b.student_id
                                                LEFT JOIN books ON books.id = b.book_id
                                                WHERE YEAR(books.publish_date) = $year AND (b.status = 0 OR r.date_return IS NULL)";

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
                                <br><br>
                                </div>
                                    </div>
                            </div>
                                </div>

                                
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="box">
                                            <div class="box-body">
                                            <div id="returnedChartContainer" style="height: 300px; width: 100%;"></div>
                              
                                <?php
                                // Fetch data for book borrowed and returned
                                $returnedQuery = "SELECT DISTINCT b.id, b.*, books.isbn, books.title, books.author, YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                                             FROM borrow b
            LEFT JOIN books ON books.id = b.book_id";

                                if ($selected_publish !== null) {
                                    $returnedQuery .= " WHERE YEAR(books.publish_date) IN ($publishYearsString)";
                                }

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
                                    $returnedYearQuery = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                                    FROM borrow b
                                    LEFT JOIN returns r ON b.book_id = r.book_id
                                    LEFT JOIN students ON students.id = b.student_id
                                    LEFT JOIN books ON books.id = b.book_id
                                                        WHERE YEAR(books.publish_date) = $year";

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
                                <br>
                                
                            </div>
                        </div>
                    </div>
                </div>

         <div class="row">
        <div class="col-xs-12">
            <div class="box">
            <div class="box-header with-border">
                 <h3 class="box-title">Contrast Returned and Pending Returns Data</h3>
            </div>
                <div class="box-body">

                    <div id="borrowReturnChartContainer" style="height: 300px; width: 100%;"></div>
                 </div>
                </div>
            </div>
        </div>
               
    

                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">All Book Transactions by Publish Year</h3>
                            </div>
                            <div class="box-body">
                                <canvas id="bookTransactionsChart" style="height:350px"></canvas>
                             
                            </div>
                        </div>
                    </div>
                </div>

               
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">Rankings of Book Publish Years by Total Transactions</h3>
                            </div>
                            <div class="box-body">
                                <ul class="list-group">
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
            </section>
        </div>
        <?php include 'includes/footer.php'; ?>
        <?php include 'includes/borrow_modal.php'; ?>
        <?php include 'includes/scripts.php'; ?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#downloadButton').click(function (e) {
                    e.preventDefault();
                    console.log("Download button clicked"); // Debug statement

                    // Capture HTML content to be converted to PDF
                    html2canvas(document.querySelector(".content-wrapper")).then(canvas => {
                        var {
                            jsPDF
                        } = window.jspdf;
                        var pdf = new jsPDF('p', 'mm', 'a4', 'landscape');

                        // Stretching the image vertically by adjusting height
                        var imgWidth = 150; // Width of the image
                        var imgHeight = 298; // Increasing the height by 50%

                        pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 0, 0, imgWidth, imgHeight);
                        pdf.save('book_transactions.pdf');
                    });
                });
            });
        </script>


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


        <script type="text/javascript">
            window.onload = function () {
                <?php
                // Fetch data for pie chart
                $pieChartData = array();
                $publishQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                        FROM borrow
                                        LEFT JOIN books ON borrow.book_id = books.id";

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

                while ($publishRow = $publishResult->fetch_assoc()) {


                    $pieChartData[] = array(
                        "label" => $publishRow['publish_year'],
                        "y" => $publishRow['count']
                    );
                }
                ?>


                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    title: {
                        text: "Pending Book Returns by Year Published of Books"
                    },
                    legend: {
                        maxWidth: 350,
                        itemWidth: 120,
        horizontalAlign: "right",
        verticalAlign: "center"
                    },
                    data: [{
                        type: "pie",
                        showInLegend: true,
                        legendText: "{label}: {y}",
                        yValueFormatString: "##0",
                        startAngle: 0,
                        dataPoints: [
                            <?php
                            // Output pie chart data
                            foreach ($pieChartData as $data) {
                                echo '{ "label": "' . $data["label"] . '", "y": ' . $data["y"] . ' },';
                            }
                            ?>
                        ]
                    }]
                });

                chart.render();

                // Pie chart for book borrowed and returned transactions
var returnedChartData = [];

<?php
                $returnedResult->data_seek(0); // Reset result pointer
                while ($returnedRow = $returnedResult->fetch_assoc()) {
                    $year = $returnedRow['publish_year'];
                    $count = $returnedRow['count'];
                    echo "returnedChartData.push({ label: '$year', y: $count });";
                }
                ?>

                var returnedChart = new CanvasJS.Chart("returnedChartContainer", {
                    animationEnabled: true,
                    title: {
                        text: "Book Borrowed and Returned by Year Published of Books"
                    },
                    legend: {
                        maxWidth: 350,
                        itemWidth: 150,
                        horizontalAlign: "right",
                     
        verticalAlign: "center",
        marginRight: 20
                    },
                    data: [{
                        type: "pie",
                        showInLegend: true,
                        legendText: "{label}: {y}",
                        startAngle: 0,
                        yValueFormatString: "##0",
                        indexLabel: "{label} {y}",
                        dataPoints: returnedChartData

                    }]


                });
                returnedChart.render();

                // Pie chart for borrow and return transactions
                var borrowReturnData = [
                    { label: "Pending Book Returns", y: <?php echo $totalTransactions; ?> },
                    { label: "Book Borrowed and Returned", y: <?php echo $totalTransactionsReturned; ?> }
                ];

                var borrowReturnChart = new CanvasJS.Chart("borrowReturnChartContainer", {
                    animationEnabled: true,
                    title: {
                        text: "Pending Book Returns vs Book Borrowed and Returned"
                    },
                    legend: {
                        maxWidth: 350,
                        itemWidth: 120
                    },
                    data: [{
                        type: "pie",
                        showInLegend: true,
                        legendText: "{label}: {y}",
                        startAngle: 0,
                        yValueFormatString: "##0",
                        indexLabel: "{label} {y}",
                        dataPoints: borrowReturnData
                    }]
                });
                borrowReturnChart.render();
            }
        </script>



        <?php include 'includes/scripts.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                            options: {
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                        
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });
            });
        </script>
</body>

</html>