<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<?php
    // Retrieve the selected student IDs and page number from the URL parameters
    $selectedStudents = isset($_GET['students']) ? json_decode($_GET['students']) : array();
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
    // Adjust the offset for the second page

    // Define the number of records per page
    $recordsPerPage = 10;

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

    // Prepare the SQL query to fetch transaction history for the student on the current page
    $transactionSql = "SELECT borrow.*, books.isbn, books.title, books.author, returns.date_return
                       FROM borrow 
                       LEFT JOIN books ON books.id = borrow.book_id 
                       LEFT JOIN returns ON borrow.student_id = returns.student_id
                       WHERE borrow.student_id = ?
                       ORDER BY borrow.date_borrow DESC
                       LIMIT $recordsPerPage OFFSET $offset";

    // Prepare and execute the statement to fetch transaction data
    $stmt = $conn->prepare($transactionSql);
    $stmt->bind_param("i", $studentIdForPage);
    $stmt->execute();
    $result = $stmt->get_result();
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
                                    <h3 class="box-title"><strong>Book Borrowing and Returning Transactions</strong></h3>
                                    <h5>Student Name: <?php echo ($studentName); ?> <br> Student ID:  <?php echo $realStudentID; ?> </h5>
                                    <!-- Add Download Button -->
                                    <div class="box-tools pull-right">
                                        <a href="#" id="downloadButton" class="btn btn-primary btn-sm">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                        <a href="#" id="downloadAllButton" class="btn btn-primary btn-sm">
                                            <i class="fa fa-download"></i> Download All
                                        </a>
                                    </div>
                                    <!-- End Download Button -->
                                </div>
                                <div class="box-body">
                                    <table class="table table-bordered table-striped" id="example1">
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
                                                // Fetch and display transaction data
                                                while($row = $result->fetch_assoc()){
                                                    echo "
                                                        <tr>
                                                            <td class='hidden'></td>
                                                            <td>".date('M d, Y', strtotime($row['date_borrow']))."</td>
                                                            <td>".($row['date_return'] ? date('M d, Y', strtotime($row['date_return'])) : "")."</td>
                                                            <td>".$row['isbn']."</td>
                                                            <td>".$row['title']."</td>
                                                            <td>".$row['author']."</td>
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

<!-- Pagination Links -->
<div class="row" style="margin-top: 20px;">
    <div class="col-sm-12 text-right">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <li <?php if ($i == $currentPage) echo 'class="active"'; ?>>
                    <a href="#" onclick="printPage(<?php echo $i; ?>)">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
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

// Function to print the content of the current page
function printCurrentPage() {
    window.print(); // Print the current page
}
function printAllPages() {
    if (<?php echo $totalPages; ?> === 1 && <?php echo count($selectedStudents); ?> === 1) {
        // If there's only one page and one student, print the current page instead of all pages
        printCurrentPage();
    } else {
        var allPagesContent = '';
        for (var i = 1; i <= <?php echo $totalPages; ?>; i++) {
            var currentURL = window.location.href;
            var cleanURL = currentURL.replace(/&?page=[^&]*/g, '');

            var xmlhttp = new XMLHttpRequest();
            xmlhttp.open("GET", cleanURL + '&page=' + i, false); // Synchronous request
            xmlhttp.send();

            allPagesContent += "<div class='page'>" + xmlhttp.responseText + "</div>";
        }

        // Instead of opening a new window and printing, we can use the same window to print all pages
        document.body.innerHTML = allPagesContent;

        // Print each page's content on a separate sheet
        window.print();

        // After printing, reload the page to restore its original content
        window.location.reload();
    }
}



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
