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
    if(isset($_SESSION['error']) && is_array($_SESSION['error']) && !empty($_SESSION['error'])){
?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-warning"></i> Error!</h4>
        <ul>
            <?php
                foreach($_SESSION['error'] as $error){
                    echo "<li>".$error."</li>";
                }
            ?>
        </ul>
    </div>
<?php
        unset($_SESSION['error']);
    }

    if(isset($_SESSION['success'])){
?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h4><i class="icon fa fa-check"></i> Success!</h4>
        <?php echo $_SESSION['success']; ?>
    </div>
<?php
        unset($_SESSION['success']);
    }
?>
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <button id="downloadButton" class="btn btn-primary btn-sm btn-flat"> Download</button>
                </div>
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
                            echo "<h2>Pending Book Returns Data for $year</h2>";
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
                        }
                        ?>
                        <div class="box-header with-border">Total Borrow Transactions: <?php echo $totalTransactions; ?></div>
                        <br><br>
                                
        <div class="row">
        <div class="col-xs-12">
            <div class="box">
                        <div id="returnedChartContainer" style="height: 300px; width: 100%;"></div>
                    </div>
                    </div>
                    </div>
                        <?php
                            // Fetch data for book borrowed and returned
                            $returnedQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                              FROM borrow
                                              INNER JOIN returns ON borrow.book_id = returns.book_id
                                              LEFT JOIN books ON borrow.book_id = books.id";

                            if ($selected_publish !== null) {
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
                            while ($returnedRow = $returnedResult->fetch_assoc()) {
                                $year = $returnedRow['publish_year'];
                                $count = $returnedRow['count'];
                                $totalTransactionsReturned += $count;


                                // Output table for book borrowed and returned for each year
                                echo "<h2>Book Borrowed and Returned Data for $year</h2>";
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
                            }
                            ?>
                              <div class="box-header with-border">Total Book Borrowed and Returned Transactions: <?php echo $totalTransactionsReturned; ?></div>
                             
                </div>
                <br>
                <div class="row">
        <div class="col-xs-12">
            <div class="box">
            <div class="box-header with-border" id="borrowReturnChartContainer" style="height: 300px; width: 100%;"></div>
                    </div>
                    </div>
                    </div>
               
                <br>
                <?php
                                // Report data analytics
                                $borrowPercentage = ($totalTransactionsReturned > 0) ? (($totalTransactions / $totalTransactionsReturned) * 100) : 0;
                                echo "<p>The total borrow transactions are: $totalTransactions, while the total book borrowed and returned transactions are: $totalTransactionsReturned.</p>";
                                echo "<p>In the selected date range, all the total book borrowing transactions are $totalTransactions. This constitutes a borrow percentage of $borrowPercentage%.</p>";
                              ?>
                              <br>
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
                    <h3 class="box-title">Rankings of Publish Years by Total Transactions</h3>
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
while ($borrowersRow = $borrowersResult->fetch_assoc()) {
$year = $borrowersRow['publish_year'];
$totalBorrowers = $borrowersRow['total_borrowers'];
$barChartData[] = array(
"label" => $year,
"y" => $totalBorrowers
);
}
                            
                            arsort($barChartData); // Sort years based on total transactions

                            $rank = 1;
                            foreach ($barChartData as $data) {
                                echo "<li class='list-group-item'>TOP $rank: Year " . $data['label'] . " - Total Borrowers: " . $data['y'] . "</li>";
                                $rank++;
                            }
                        ?>
                    </ul>
                </div>
            </div>
                        </div>
                        </div>
        

        
    </div>
    
    </div>
    </section>   
  </div>
                
<?php include 'includes/footer.php'; ?>
<?php include 'includes/borrow_modal.php'; ?>
<?php include 'includes/scripts.php'; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script>
$(function(){
  $(document).on('click', '#append', function(e){
    e.preventDefault();
    $('#append-div').append(
      '<div class="form-group"><label for="" class="col-sm-3 control-label">ISBN</label><div class="col-sm-9"><input type="text" class="form-control" name="isbn[]"></div></div>'
    );
  });
});
</script>
<script>
$(document).ready(function() {
    $('#downloadButton').click(function(e) {
        e.preventDefault();

        // Open a new window with only the table content
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Borrow Data</title>');
        printWindow.document.write('<style>table { border-collapse: collapse; width: 100%; } table, th, td { border: 1px solid black; }</style>');
        printWindow.document.write('</head><body>');
        <?php
            // Loop through tables and append HTML to the print window
            $publishResult->data_seek(0); // Reset result pointer
            while ($publishRow = $publishResult->fetch_assoc()) {
                $year = $publishRow['publish_year'];
                echo "printWindow.document.write('<h2>Pending Book Returns Data for $year</h2>');";
                echo "printWindow.document.write($('#borrow_table_$year').clone().prop('outerHTML'));";
            }
            // Loop through returned tables and append HTML to the print window
            $returnedResult->data_seek(0); // Reset result pointer
            while ($returnedRow = $returnedResult->fetch_assoc()) {
                $year = $returnedRow['publish_year'];
                echo "printWindow.document.write('<h2>Book Borrowed and Returned Data for $year</h2>');";
                echo "printWindow.document.write($('#returned_table_$year').clone().prop('outerHTML'));";
            }
        ?>
        printWindow.document.write('<div>Total Borrow Transactions: <?php echo $totalTransactions; ?></div>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();

        // Call the print function on the new window
        printWindow.print();
    });
});
</script>
<script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


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
        itemWidth: 120
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
            itemWidth: 120
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
$(document).ready(function() {
    // Fetch data for book transactions by publish year
    $.ajax({
        url: 'fetch_book_transactions.php', // Path to your PHP script to fetch data
        method: 'GET',
        success: function(data) {
            var years = [];
            var transactions = [];

            // Parse the JSON data received
            data.forEach(function(item) {
                years.push(item.publish_year);
                transactions.push(item.total_transactions);
            });

            // Render the bar chart using Chart.js
            var ctx = document.getElementById('bookTransactionsChart').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: years,
                    datasets: [{
                        label: 'Book Transactions',
                        data: transactions,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)', // Adjust color as needed
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
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
        }
    });
});
</script>



</body>
</html>
