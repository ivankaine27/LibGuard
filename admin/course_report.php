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
              <table id="borrowed_book_data" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Date Borrowed</th>
                  <th>Date Returned</th>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>ISBN</th>
                  <th>Title</th>
                  <th>Course</th>
                  <th>Status</th>
                </thead>
                <tbody>
                <?php
                    $selected_courses = isset($_GET['selected_courses']) ? $_GET['selected_courses'] : null;
                    $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
                    $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;
                    $sql = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.lastname, students.course_id, books.isbn, books.title, books.author, course.title as course_title
                            FROM borrow b
                            LEFT JOIN returns r ON b.book_id = r.book_id
                            LEFT JOIN students ON students.id = b.student_id
                            LEFT JOIN books ON books.id = b.book_id
                            LEFT JOIN course ON students.course_id = course.id";

                    if ($selected_courses !== null) {
                        // Include the course filter when it's provided
                        $courseIds = explode(',', $selected_courses);
                        $courseIds = array_map('intval', $courseIds);  // Convert string values to integers
                        $courseIdsString = implode(',', $courseIds);

                        $sql .= " WHERE course.id IN ($courseIdsString)";
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
                                <td>" . $row['course_title'] . "</td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>
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

        // Function to format the current date and time
        function getCurrentDateTime() {
            const now = new Date();
            const options = { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };
            return now.toLocaleDateString('en-US', options);
        }

        // Open a new window with only the table content and pie chart
        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>Borrow Data</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('table { border-collapse: collapse; width: 50%; border: 1px solid #ddd; page-break-inside: avoid; }');
        printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }');
        printWindow.document.write('th { background-color: #f2f2f2; }');
        // Styles for ribbon (both for screen and print)
        printWindow.document.write('.ribbon-container { position: relative; background-color: #800000; padding: 10px; height: 50px; }');
        printWindow.document.write('.ribbon-text { color: #fff; position: absolute; bottom: 10px; right: 10px; font-size: 18px; width: 20%; text-align: right; }');
        printWindow.document.write('.date-time { color: #fff; position: absolute; bottom: 10px; left: 10px; font-size: 18px; width: 80%; text-align: left; }');
        // Media print styles to show ribbon when printing
        printWindow.document.write('<style>@media print { .ribbon-container { display: block; } }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="ribbon-container"><p class="ribbon-text"><b>TRANSACTION REPORT</b</p><p class="date-time">' + getCurrentDateTime() + '</p></div>');
        printWindow.document.write('<h2 style="margin-top: 10px;">Book Borrow and Returning Transaction</h2>');
        printWindow.document.write('<div id="chartContainer" style="height: 300px; width: 100%;"></div>');
        printWindow.document.write('<div id="tableContainer">' + $('#example1').clone().prop('outerHTML') + '</div>');


        printWindow.document.write('<script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>');
        printWindow.document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>');
        printWindow.document.write('<script>');

        printWindow.document.write('window.onload = function () {');
        printWindow.document.write('var pieChartData = ' + '<?php echo json_encode($pieChartData); ?>' + ';');
        printWindow.document.write('pieChartData = JSON.parse(pieChartData.replace(/&quot;/g, \'"\'));');  // Handle HTML encoding
        printWindow.document.write('var chart = new CanvasJS.Chart("chartContainer", {');
        printWindow.document.write('title: {text: "Borrowed Books by Course"},');
        printWindow.document.write('legend: {maxWidth: 350, itemWidth: 120},');
        printWindow.document.write('data: [{type: "pie", showInLegend: true, legendText: "{indexLabel}", dataPoints: pieChartData }]});');
        printWindow.document.write('chart.render();');
        printWindow.document.write('}');
        printWindow.document.write('</script>');

        printWindow.document.write('</body></html>');



        printWindow.document.close();

        // Call the print function on the new window
        // printWindow.print();
    });
});
</script>

<!-- <script type="text/javascript">
  window.onload = function () {
	var chart = new CanvasJS.Chart("chartContainer",
	{
		title:{
			text: "Gaming Consoles Sold in 2012"
		},
		legend: {
			maxWidth: 350,
			itemWidth: 120
		},
		data: [
		{
			type: "pie",
			showInLegend: true,
			legendText: "{indexLabel}",
			dataPoints: [
				{ y: 4181563, indexLabel: "PlayStation 3" },
				{ y: 2175498, indexLabel: "Wii" },
				{ y: 3125844, indexLabel: "Xbox 360" },
				{ y: 1176121, indexLabel: "Nintendo DS"},
				{ y: 1727161, indexLabel: "PSP" },
				{ y: 4303364, indexLabel: "Nintendo 3DS"},
				{ y: 1717786, indexLabel: "PS Vita"}
			]
		}
		]
	});
	chart.render();
}
</script> -->
<script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script type="text/javascript">
  window.onload = function () {
    <?php
    // Fetch data for pie chart
    $pieChartData = array();
    $courseQuery = "SELECT course.title, COUNT(*) AS count
                    FROM borrow
                    LEFT JOIN students ON borrow.student_id = students.id
                    LEFT JOIN course ON students.course_id = course.id";

    if ($selected_courses !== null) {
        $courseIds = explode(',', $selected_courses);
        $courseIds = array_map('intval', $courseIds);
        $courseIdsString = implode(',', $courseIds);

        $courseQuery .= " WHERE course.id IN ($courseIdsString)";
    }

    // Add the date range condition
    if ($startDate && $endDate) {
        $courseQuery .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
    }

    $courseQuery .= " GROUP BY course.title";
    $courseResult = $conn->query($courseQuery);

    while ($courseRow = $courseResult->fetch_assoc()) {
        $pieChartData[] = array(
            "label" => $courseRow['title'],
            "y" => $courseRow['count']
        );
    }
    ?>
    
    var chart = new CanvasJS.Chart("chartContainer", {
      title: {
        text: "Borrowed Books by Course"
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

        table = $('#borrowed_book_data').DataTable({
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
                        var title = 'Borrowed Books by Course';
                        
                        // Add title, student name, and student ID to separate rows
                        sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr></table>';
                        
                        // Add an empty row for better formatting
                        sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr></tr></table>';
                    },
                    filename: 'Borrowed Books by Course' // Set the filename for download
                },

                {
                    extend: 'csvHtml5',
                    class: 'buttons-csv',
                    init: function (api, node, config) {
                        $(node).hide()
                    },
                    customize: function (csv) {
                        // Add the title and student information to the CSV content
                        var csvContent = 'Borrowed Books by Course\n';

                        // Append the existing CSV content
                        csvContent += csv;

                        return csvContent;
                    },
                    filename: 'Borrowed Books by Course' // Set the filename for download
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
                                { text: 'Borrowed Books by Course\n', fontSize: 14, bold: true },
                            ],
                            alignment: 'left',
                            margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                        });
                    },
                    filename: 'Borrowed Books by Course' // Set the filename for download
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




</body>
</html>
