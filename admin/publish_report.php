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
                        </div>
                        
                        <div class="box-body">
                        <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                        <table id="example1" class="table table-bordered">
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
            $selected_publish = isset($_GET['selected_publish']) ? $_GET['selected_publish'] : null;
            $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
            $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;

            $sql = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, books.isbn, books.title, books.author
                    FROM borrow b
                    LEFT JOIN returns r ON b.book_id = r.book_id
                    LEFT JOIN students ON students.id = b.student_id
                    LEFT JOIN books ON books.id = b.book_id
                    LEFT JOIN course ON students.course_id = course.id
                    LEFT JOIN category ON books.category_id = category.id";

            if ($selected_publish !== null) {
                // Include the publish year filter when it's provided
                $publishYears = explode(',', $selected_publish);
                $publishYears = array_map('intval', $publishYears);  // Convert string values to integers
                $publishYearsString = implode(',', $publishYears);

                $sql .= " WHERE YEAR(books.publish_date) IN ($publishYearsString)";
            }

            if ($startDate && $endDate) {
                $sql .= " AND b.date_borrow BETWEEN '$startDate' AND '$endDate'";
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
    </section>   
  </div>
    
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/borrow_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<!-- Include jsPDF library -->
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
<script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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



</body>
</html>
