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
                        <button id="downloadPdf" class="btn btn-primary">Download Report</button>
                    </div>
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

                </div>
            </section>
        </div>
    </div>
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

  
</body>
</html>

<?php
     function includeReturnedBooks() {
        include 'includes/conn.php';
    
        // Validate and sanitize input parameters
        $startDate = isset($_GET['startDate']) ? date('Y-m-d', strtotime($_GET['startDate'])) : null;
        $endDate = isset($_GET['endDate']) ? date('Y-m-d', strtotime($_GET['endDate'])) : null;
        // Prepare the SQL query with the date filter
        $sql_returned = "SELECT *, students.student_id AS stud, borrow.status AS barstat
                         FROM borrow
                         LEFT JOIN students ON students.id = borrow.student_id
                         LEFT JOIN books ON books.id = borrow.book_id
                         WHERE borrow.status = 1";
    
        if ($startDate && $endDate) {
            $sql_returned .= " AND date_borrow BETWEEN '$startDate' AND '$endDate'";
        }
    
        $sql_returned .= " ORDER BY date_borrow DESC";
    
        // Execute the SQL query
        $query_returned = $conn->query($sql_returned);
    
        // Fetch and display the results
        while ($row = $query_returned->fetch_assoc()) {
            echo "
                <tr>
                    <td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>
                    <td>" . $row['stud'] . "</td>
                    <td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>
                    <td>" . $row['isbn'] . "</td>
                    <td>" . $row['title'] . "</td>
                    <td><span class='label label-success'>Returned</span></td>
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
        $sql_not_returned = "SELECT *, students.student_id AS stud, borrow.status AS barstat
                             FROM borrow
                             LEFT JOIN students ON students.id = borrow.student_id
                             LEFT JOIN books ON books.id = borrow.book_id
                             WHERE borrow.status = 0";
    
        if ($startDate && $endDate) {
            $sql_not_returned .= " AND date_borrow BETWEEN '$startDate' AND '$endDate'";
        }
    
        $sql_not_returned .= " ORDER BY date_borrow DESC";
    
        // Execute the SQL query
        $query_not_returned = $conn->query($sql_not_returned);
    
        // Fetch and display the results
        while ($row = $query_not_returned->fetch_assoc()) {
            echo "
                <tr>
                    <td>" . date('M d, Y', strtotime($row['date_borrow'])) . "</td>
                    <td>" . $row['stud'] . "</td>
                    <td>" . $row['firstname'] . ' ' . $row['lastname'] . "</td>
                    <td>" . $row['isbn'] . "</td>
                    <td>" . $row['title'] . "</td>
                    <td><span class='label label-danger'>Not Returned</span></td>
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
            $sql_books = "SELECT *, students.student_id AS stud, borrow.status AS barstat FROM borrow LEFT JOIN students ON students.id=borrow.student_id LEFT JOIN books ON books.id=borrow.book_id WHERE DATE(date_borrow) = '$date' ORDER BY date_borrow DESC";
            $query_books = $conn->query($sql_books);
            while($book_row = $query_books->fetch_assoc()) {
                $status_label = $book_row['barstat'] ? "<span class='label label-success'>Returned</span>" : "<span class='label label-danger'>Not Returned</span>";
                echo "<tr>";
                echo "<td>".date('M d, Y', strtotime($book_row['date_borrow']))."</td>";
                echo "<td>".$book_row['stud']."</td>";
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
