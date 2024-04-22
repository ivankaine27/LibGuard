
<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<?php
    // Retrieve the selected student IDs and page number from the URL parameters
    $selectedStudents = isset($_GET['students']) ? json_decode($_GET['students']) : array();
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
    // Adjust the offset for the second page

    // Define the number of records per page
    $recordsPerPage = 100;

    // Calculate the total number of pages needed (equal to the number of selected students)
    $totalPages = count($selectedStudents);

    // Calculate the offset for the current page
    $offset = ($currentPage - 1) * $recordsPerPage;
    if ($currentPage > 1) {
        $offset = 0;
    }
    // Get the ID of the student for the current page
    $studentIdForPage = isset($selectedStudents[$currentPage - 1]) ? $selectedStudents[$currentPage - 1] : null;

    // Prepare the SQL query to fetch student name and ID
    $studentSql = "SELECT firstname, lastname, student_id as real_student_id
                   FROM students
                   WHERE id = ?";

    // Prepare and execute the statement to fetch student name and ID
    $stmt = $conn->prepare($studentSql);
    $stmt->bind_param("i", $studentIdForPage);
    $stmt->execute();
    $studentResult = $stmt->get_result();

    // Fetch the student name and ID
    $studentData = $studentResult->fetch_assoc();
    $studentName = $studentData['firstname'] . ' ' . $studentData['lastname'];
    $realStudentID = $studentData['real_student_id'];

    $sql = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, books.isbn, books.title, books.author
            FROM borrow b
            LEFT JOIN returns r ON b.book_id = r.book_id
            LEFT JOIN books ON books.id = b.book_id
            WHERE b.student_id = '$studentIdForPage'
            ORDER BY b.date_borrow DESC
            LIMIT $recordsPerPage OFFSET $offset";

?>







<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-maroon-gold sidebar-mini">
    <div class="wrapper">
        <?php include 'includes/navbar.php'; ?>
        <?php include 'includes/menubar.php'; ?>
        <div class="content-wrapper">
            <div class="container">
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <div class="col-sm-10 col-sm-offset-1">
                            <div class="box">
                                <div class="box-header with-border">
                                    <!-- Display student name and ID -->
                                    <div class="image-container row" hidden>
                                        <div class="col-md-12">
                                            <img src="../images/libguard-logo.png" style="width: 30%; margin-left: 25rem ; margin-bottom: -12%; margin-top: -10%;">
                                        </div>
                                    </div>
                                    <h3 class="box-title"><strong>Book Borrowing and Returning Transactions</strong></h3>
                                    <h5 class="student-info">Student Name: <?php echo ($studentName); ?> <br> Student ID:  <?php echo $realStudentID; ?> </h5>
                                    <!-- Add Download Button -->
                                    <div class="box-tools pull-right">
                                        <button class="btn btn-primary btn-sm" id="excel-btn">
                                            <i class="fa fa-download"></i> Excel
                                        </button>
                                        <button class="btn btn-primary btn-sm" id="csv-btn">
                                            <i class="fa fa-download"></i> CSV
                                        </button>
                                        <button class="btn btn-primary btn-sm" id="pdf-btn">
                                            <i class="fa fa-download"></i> PDF
                                        </button>
                                        <a href="#" id="downloadButton" class="btn btn-primary btn-sm">
                                            <i class="fa fa-download"></i> Print
                                        </a>
                                        <a href="#" id="downloadAllButton" class="btn btn-primary btn-sm">
                                            <i class="fa fa-download"></i> Download All
                                        </a>
                                        </a>
                                    </div>
                                    <!-- End Download Button -->
                                </div>
                                <div class="box-body">
                                 <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="customer_data">
                                        <thead>
                                            <th class="hidden"></th>
                                            <th><strong>Date Borrowed</strong></th>
                                            <th><strong>Date Returned</strong></th>
                                            <th><strong>ISBN</strong></th>
                                            <th><strong>Title</strong></th>
                                            <th><strong>Author</strong></th>
                                        </thead>
                                        <tbody>
                                        <?php
// Assuming you have already established a database connection and $result holds the query result.

$data = array();
$query = $conn->query($sql);
while($row = $query->fetch_assoc()) {
    // Add transaction data to the $data array for JSON encoding
    $transaction = array(
        'date_borrow' => date('M d, Y', strtotime($row['date_borrow'])),
        'date_return' => $row['return_date'] ? date('M d, Y', strtotime($row['return_date'])) : "Not Returned Yet",
        'isbn' => $row['isbn'],
        'title' => $row['title'],
        'author' => $row['author']
    );
    $data[] = $transaction;
    
    // Echo HTML table row for each transaction
    echo "
        <tr>
            <td class='hidden'></td>
            <td>".$transaction['date_borrow']."</td>
            <td>".$transaction['date_return']."</td>
            <td>".$transaction['isbn']."</td>
            <td>".$transaction['title']."</td>
            <td>".$transaction['author']."</td>
        </tr>
    ";
}

// Encode $data array as JSON
$json_data = json_encode($data);

// Now you can use $json_data for any other purpose, such as sending it via AJAX

?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
<!-- Pagination Links -->
<div class="row" style="margin-top: 20px;">
    <div class="col-sm-12 text-right">
        <ul class="pagination">
            <?php 
            // Calculate previous and next page numbers
            $prevPage = ($currentPage > 1) ? $currentPage - 1 : 1;
            $nextPage = ($currentPage < $totalPages) ? $currentPage + 1 : $totalPages;

            // Display previous page link
            ?>
            <li>
                <a href="?students=<?php echo urlencode(json_encode($selectedStudents)); ?>&page=<?php echo $prevPage; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php
            // Display pagination links
            for ($i = 1; $i <= $totalPages; $i++) {
                ?>
                <li <?php if ($i == $currentPage) echo 'class="active"'; ?>>
                    <a href="?students=<?php echo urlencode(json_encode($selectedStudents)); ?>&page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>

            <?php
            // Display next page link
            ?>
            <li>
                <a href="?students=<?php echo urlencode(json_encode($selectedStudents)); ?>&page=<?php echo $nextPage; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </div>
</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script type="text/javascript" src="js/script.js"></script>
<!-- End Pagination Links -->

                </section>
            </div>
        </div>
        <?php include 'includes/footer.php'; ?>
    </div>
    <?php include 'includes/scripts.php'; ?>

<script>
document.getElementById('downloadButton').addEventListener('click', function(event) {
    event.preventDefault();
    printCurrentPage(); // Print the current page content
});

document.getElementById('downloadAllButton').addEventListener('click', function(event) {
    event.preventDefault();
    printAllPages(); // Print all pages content
});

document.getElementById('downloadCSVButton').addEventListener('click', function(event) {
    event.preventDefault();
    generateCSV(); // Generate and download CSV
});

// Function to print the content of the current page
function printCurrentPage() {
    var originalContent = document.body.innerHTML; // Save the original content
    document.body.innerHTML = '<div id="printableContent">' + getPrintableContent() + '</div>'; // Replace with the printable content

    window.print(); // Print the current page

    document.body.innerHTML = originalContent; // Restore the original content
}

// Function to generate CSV version of the table
function generateCSV() {
    var csvContent = 'Date Borrowed,Date Returned,ISBN,Title,Author\n';
    
    // Iterate over table rows
    var tableRows = document.querySelectorAll('#customer_data tbody tr');
    tableRows.forEach(function(row) {
        var columns = row.querySelectorAll('td');
        csvContent += columns[1].textContent + ','; // Date Borrowed
        csvContent += columns[2].textContent + ','; // Date Returned
        csvContent += columns[3].textContent + ','; // ISBN
        csvContent += columns[4].textContent + ','; // Title
        csvContent += columns[5].textContent + '\n'; // Author
    });

    // Create a Blob and trigger a download
    var blob = new Blob([csvContent], { type: 'text/csv' });
    var link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = 'transaction_data.csv';
    link.click();
}

function getPrintableContent() {

    var image = $('.image-container').html();

    var printableContent = '<div>'+ image +'<hr style="border: 1rem solid #800000;"><h3>Book Borrowing and Returning Transactions</h3>';
    printableContent += '<p>Student Name: <?php echo ($studentName); ?><br> Student ID: <?php echo $realStudentID; ?></p></div>';
    printableContent += getTableHtml();
    printableContent += '<hr style="border: 1rem solid #800000; margin-top: 90%; !important">';
    return printableContent;
}
function getTableHtml() {
    // Create a div element and append the table content to it
    var container = document.createElement('div');
    container.innerHTML = document.getElementById('customer_data').outerHTML;

    // Remove pagination links if they exist
    var paginationLinks = container.querySelectorAll('.pagination');
    for (var i = 0; i < paginationLinks.length; i++) {
        paginationLinks[i].parentNode.removeChild(paginationLinks[i]);
    }

    // Return the innerHTML of the container
    return container.innerHTML;
}

function printAllPages() {
    var originalContent = document.body.innerHTML; // Save original content

    if (<?php echo $totalPages; ?> === 1 && <?php echo count($selectedStudents); ?> === 1) {
        // If there's only one page and one student, print the current page instead of all pages
        printCurrentPage();
    } else {
        for (var i = 1; i <= <?php echo $totalPages; ?>; i++) {
            var currentURL = window.location.href;
            var cleanURL = currentURL.replace(/&?page=[^&]*/g, '');

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.open("GET", cleanURL + '&page=' + i, false); // Synchronous request
            xmlhttp.send();

            // Create a temporary div element to hold the fetched content
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = xmlhttp.responseText;

            // Find the table element within the fetched content
            var tableElement = tempDiv.querySelector('.box-body table');
            var studentInfo = tempDiv.querySelector('.student-info');
            var image = tempDiv.querySelector('.image-container');

            // Create a temporary page content with header and table content
            var pageContent = '<div>'+ image.innerHTML +'<hr style="border: 1rem solid #800000;"><h3>Book Borrowing and Returning Transactions</h3>';
            pageContent += studentInfo.innerHTML;
            pageContent += '<div class="box-body">' + (tableElement ? tableElement.outerHTML : '<tbody><tr><td colspan="5" class="text-center fw-bold">No Data Available</td></tr></tbody>') + '</div>';

            // Replace current page content with the temporary page content
            document.body.innerHTML = pageContent + '<hr style="border: 1rem solid #800000; margin-top: 90%; !important">';

            // Print the content
            window.print();
        }

        // Restore original content after printing
        document.body.innerHTML = originalContent;
    }
}


</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
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

<script type="text/javascript" language="javascript">
    $(document).ready(function () {
        // Base64 encoded image data
        <?php
        // Path to your image file
        $imagePath = '../images/logos/libguard-logo-header2.png';

        // Read image data
        $imageData = file_get_contents($imagePath);

        // Encode image data to base64
        $imgData = base64_encode($imageData);
        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
        $src = 'data:image/' . $type . ';base64,' . $imgData;
        ?>

        var image = '<?php echo $src; ?>';
        var table;

        table = $('#customer_data').DataTable({
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
                        var title = 'Book Borrowing and Returning Transactions';
                        var studentName = 'Student Name: <?php echo $studentName; ?>';
                        var studentID = 'Student ID: <?php echo $realStudentID; ?>';
                        
                        // Add title, student name, and student ID to separate rows
                        sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr><td colspan="5"><b>' + title + '</b></td></tr><tr><td colspan="5">' + studentName + '</td></tr><tr><td colspan="5">' + studentID + '</td></tr></table>';
                        
                        // Add an empty row for better formatting
                        sheet.getElementsByTagName('worksheet')[0].appendChild(document.createElement("table")).outerHTML = '<table><tr></tr></table>';
                    },
                    filename: 'Student Transaction History' // Set the filename for download
                },

                {
                    extend: 'csvHtml5',
                    class: 'buttons-csv',
                    init: function (api, node, config) {
                        $(node).hide()
                    },
                    customize: function (csv) {
                        // Add the title and student information to the CSV content
                        var csvContent = 'Book Borrowing and Returning Transactions\n';
                        csvContent += 'Student Name: <?php echo $studentName; ?>\n';
                        csvContent += 'Student ID: <?php echo $realStudentID; ?>\n\n';

                        // Append the existing CSV content
                        csvContent += csv;

                        return csvContent;
                    },
                    filename: 'Student Transaction History' // Set the filename for download
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
                                { text: 'Book Borrowing and Returning Transactions\n', fontSize: 14, bold: true },
                                { text: 'Student Name: <?php echo $studentName; ?>\n', fontSize: 10 },
                                { text: 'Student ID: <?php echo $realStudentID; ?>\n\n', fontSize: 10 }
                            ],
                            alignment: 'left',
                            margin: [0, 0, 15, 15] // Adjust left margin for alignment and add space before the table
                        });
                    },
                    filename: 'Student Transaction History' // Set the filename for download
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

<style>
    .page {
        page-break-after: always;
    }
    .pagination {
        margin: 0;
        padding: 0;
    }

    .pagination li {
        display: inline;
        margin-left: 5px;
    }

    .pagination a {
        text-decoration: none;
    }

</style>


</body>
</html>
