<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-maroon-gold sidebar-mini">

<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- <section class="content-header">
      <h1>
        Borrow Books
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Transaction</li>
        <li class="active">Borrow</li>
      </ol>
    </section> -->
    <!-- Main content -->
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
              <!-- <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Borrow</a> -->
              <button id="downloadButton" class="btn btn-primary btn-sm btn-flat"> Download</button>
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
            
            <div class="box-body">
            <div id="chartContainer" style="height: 300px; width: 100%;"></div>
              <table class="table table-bordered" id="book_data">
                <thead>
                  <th class="hidden"></th>
                  <th>Date Borrowed</th>
                  <th>Date Returned</th>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>ISBN</th>
                  <th>Title</th>
                  <th>Status</th>
                </thead>
                <tbody>
                <?php
                    $selected_category = isset($_GET['selected_category']) ? $_GET['selected_category'] : null;
                    $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                    $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;
                    $sql = "SELECT
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
                              LEFT JOIN category on books.category_id = category.id";


                    if ($selected_category !== null) {
                        // Include the course filter when it's provided
                        $categoryIds = explode(',', $selected_category);
                        $categoryIds = array_map('intval', $categoryIds);  // Convert string values to integers
                        $categoryIdsString = implode(',', $categoryIds);

                        $sql .= " WHERE category.id IN ($categoryIdsString)";
                    }
                    if ($startDate && $endDate) {
                      $sql .= " AND date_borrow BETWEEN '$startDate' AND '$endDate'";
                    }

                    $sql .= " ORDER BY b.date_borrow DESC";

                    $query = $conn->query($sql);

                    while ($row = $query->fetch_assoc()) {
                        $status = ($row['status']) ? '<span class="label label-success">returned</span>' : '<span class="label label-danger">not returned</span>';
                        echo "
                            <tr>
                                <td class='hidden'></td>
                                <td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>
                                <td>" . ($row['return_date'] ? date('M d, Y', strtotime($row['return_date'])) : "Not Returned Yet") . "</td>
                                <td>" . $row['stud'] . "</td>
                                <td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>
                                <td>" . $row['isbn'] . "</td>
                                <td>" . $row['title'] . "</td>
                                <td>" . $status . "</td>
                            </tr>
                        ";
                    }
                    ?>
                </tbody>
              </table>
            </div>
          </div>
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
                                course.*
                            FROM
                                borrow
                                LEFT JOIN books ON borrow.book_id = books.id
                                LEFT JOIN students ON borrow.student_id = students.id
                                LEFT JOIN course ON students.course_id = course.id";
                                
            $selected_category = isset($_GET['selected_category']) ? $_GET['selected_category'] : null;
            $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
            $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

            if ($selected_category !== null) {
                $publishCategories = explode(',', $selected_category);
                $publishCategories = array_map('intval', $publishCategories);
                $publishCategoriesString = implode(',', $publishCategories);

                $publishQuery .= " WHERE students.course_id IN ($publishCategoriesString)";
            }

            // Add the date range condition
            if ($startDate && $endDate) {
                $publishQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
            }

            $publishQuery .= " AND borrow.status = 0";
            $publishQuery .= " GROUP BY course_id";
            $publishResult = $conn->query($publishQuery);

            // Fetch data for each year and create tables for pending book returns
            $totalTransactions = 0;
            while ($publishRow = $publishResult->fetch_assoc()) {
                $course = $publishRow['title'];
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

      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-body">
              <div class="row">
                <div class="col-md-12">
                  <div id="borrowReturnChartContainer" style="height: 300px; width: 100%;"></div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-12">
                  <?php
                    // Fetch data for book borrowed and returned
                    $returnedQuery = "SELECT DISTINCT b.id, b.*, books.isbn, books.title, books.author, YEAR(books.publish_date) AS publish_year, COUNT(*) AS count
                                                  FROM borrow b
                                                  LEFT JOIN books ON books.id = b.book_id";

                    if ($selected_category !== null) {
                        $returnedQuery .= " WHERE YEAR(books.publish_date) IN ($publishCategoriesString)";
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
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">All Book Transactions by Courses</h3>
            </div>
            <div class="box-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="box-body">
                  <div class="row">
                    <div class="col-md-12">
                      <canvas id="bookTransactionsChart" style="height:350px"></canvas>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Rankings of Book Courses</h3>
                </div>
                <div class="box-body">
                    <ul class="list-group">
                      <?php
                        // Fetch data for all borrowers grouped by publish year
                        $borrowersQuery = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS total_borrowers, course.*
                                            FROM borrow
                                            LEFT JOIN books ON borrow.book_id = books.id
                                            LEFT JOIN students ON borrow.student_id = students.id
                                            LEFT JOIN course ON students.course_id = course.id
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
                            $year = $borrowersRow['title'];
                            $totalBorrowers = $borrowersRow['total_borrowers'];
                            $barChartData[$year] = $totalBorrowers;
                            $allYears[] = $year;
                        }

                        // Add selected publish years with 0 transactions
                        if (!empty($selected_category)) {
                            $selectedYears = explode(',', $selected_category);
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
                                $yearsWithTransactions[] = "Course $year - Total Borrowers: $transactions";
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
</div>
<?php include 'includes/scripts.php'; ?>
<!-- Include jsPDF library -->
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
$(function(){
  $(document).on('click', '#append', function(e){
    e.preventDefault();
    $('#append-div').append(
      '<div class="form-group"><label for="" class="col-sm-3 control-label">ISBN</label><div class="col-sm-9"><input type="text" class="form-control" name="isbn[]"></div></div>'
    );
  });
});
</script>
<!-- <script>
    $(document).ready(function() {
        $('#downloadButton').click(function(e) {
            e.preventDefault();

            // Extract table data
            var tableData = [];
            $('#example1 tbody tr').each(function(row, tr) {
                tableData[row] = {
                    'Date Borrowed': $(tr).find('td:eq(1)').text(),
                    'Date Returned': $(tr).find('td:eq(2)').text(),
                    'Student ID': $(tr).find('td:eq(3)').text(),
                    'Name': $(tr).find('td:eq(4)').text(),
                    'ISBN': $(tr).find('td:eq(5)').text(),
                    'Title': $(tr).find('td:eq(6)').text(),
                    'Status': $(tr).find('td:eq(7)').text(),
                };
            });

            // Convert table data to CSV format
            var csvContent = 'data:text/csv;charset=utf-8,';
            tableData.forEach(function(row) {
                csvContent += Object.values(row).join(',') + '\n';
            });

            // Create a Blob and create a link to trigger the download
            var blob = new Blob([csvContent], { type: 'text/csv' });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = 'borrow_data.csv';
            link.click();
        });
    });
</script> -->
<!-- Add this script after the existing scripts in your HTML -->
<script>
    $(document).ready(function() {
        $('#downloadButton').click(function(e) {
            e.preventDefault();

            // Open a new window with only the table content
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Borrow Data</title>');
            printWindow.document.write('<style>table { border-collapse: collapse; width: 100%; } table, th, td { border: 1px solid black; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h2>Borrow Data</h2>');
            printWindow.document.write($('#example1').clone().prop('outerHTML'));
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            // Call the print function on the new window
            printWindow.print();
        });
    });
</script>

<script type="text/javascript">
  window.onload = function () {
    <?php
      // Fetch data for pie chart
      $pieChartData = array();
      $categoryQuery = "SELECT category.name, COUNT(*) AS count
                        FROM borrow
                        LEFT JOIN books ON borrow.book_id = books.id
                        LEFT JOIN category ON books.category_id = category.id";

      if ($selected_category !== null) {
          $categoryIds = explode(',', $selected_category);
          $categoryIds = array_map('intval', $categoryIds);
          $categoryIdsString = implode(',', $categoryIds);

          $categoryQuery .= " WHERE category.id IN ($categoryIdsString)";
      }

      // Add the date range condition
      if ($startDate && $endDate) {
        $categoryQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
      }

      $categoryQuery .= " GROUP BY category.name";
      $categoryResult = $conn->query($categoryQuery);

      while ($categoryRow = $categoryResult->fetch_assoc()) {
          $pieChartData[] = array(
              "label" => $categoryRow['name'],
              "y" => $categoryRow['count']
          );
      }

    ?>

    var chart = new CanvasJS.Chart("chartContainer", {
      title: {
        text: "Borrowed Books by Category"
      },
      legend: {
        maxWidth: 350,
        itemWidth: 120
      },
      data: [{
        type: "pie",
        showInLegend: true,
        legendText: "{indexLabel}",
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
  }
</script>

<script type="text/javascript" language="javascript">
    $(document).ready(function () {
        // Base64 encoded image data
        <?php
        // Path to your image file
        $imagePath = '../images/libguard-logo-header2.png';

        // Read image data
        $imageData = file_get_contents($imagePath);

        // Encode image data to base64
        $imgData = base64_encode($imageData);
        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
        $src = 'data:image/' . $type . ';base64,' . $imgData;
        ?>

        var image = '<?php echo $src; ?>';
        var table;

        table = $('#book_data').DataTable({
            dom: 'lBfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    class: 'buttons-excel',
                    init: function (api, node, config) {
                        $(node).hide()
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
                    class: 'buttons-csv',
                    init: function (api, node, config) {
                        $(node).hide()
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
                    class: 'buttons-pdf',
                    init: function (api, node, config) {
                        $(node).hide()
                    },
                    customize: function (doc) {
                        // Remove the title
                        doc.content.splice(0, 1);

                        // Add the image to the PDF document
                        doc.content.unshift({
                            margin: [0, 0, 0, 12],
                            alignment: 'center',
                            image: image
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
                },
            ],
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });

        // Trigger Excel export
        $('#excel-btn').click(function() {
            table.buttons('.buttons-excel').trigger();
        });

        // Trigger CSV export
        $('#csv-btn').click(function() {
            table.buttons('.buttons-csv').trigger();
        });

        // Trigger PDF export
        $('#pdf-btn').click(function() {
            table.buttons('.buttons-pdf').trigger();
        });
    });
</script>

<script>
  window.onload = function () {
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
                  // options: {
                  //     scales: {
                  //         yAxes: [{
                  //             ticks: {
                  //                 beginAtZero: true
                  //             }
                  //         }]
                  //     }
                  // }
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
