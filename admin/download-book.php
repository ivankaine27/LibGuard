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
                        <button onclick="downloadPDF()" class="btn btn-primary">Download Report</button>
                    </div>
                    <div class="returned-books-container">
                        <div class="box">
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
                        <div class="box">
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
      
                    <div class="dates-container">
                        <?php includeBookReports(); ?>

                </div>
            </section>
        </div>
    </div>
    <script>
    function downloadPDF() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "generate_pdf.php", true);
        xhr.responseType = "arraybuffer"; // Set response type to arraybuffer
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var blob = new Blob([xhr.response], { type: "application/pdf" });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = 'yearly_library_book_report.pdf';
                link.click();
            }
        };
        xhr.send();
    }
</script>

  
</body>
</html>

<?php
    function includeReturnedBooks() {
        include 'includes/conn.php';
        $sql_returned = "SELECT *, students.student_id AS stud, borrow.status AS barstat FROM borrow LEFT JOIN students ON students.id=borrow.student_id LEFT JOIN books ON books.id=borrow.book_id WHERE borrow.status = 1 ORDER BY date_borrow DESC";
        $query_returned = $conn->query($sql_returned);
        while($row = $query_returned->fetch_assoc()){
            echo "
                <tr>
                    <td>".date('M d, Y', strtotime($row['date_borrow']))."</td>
                    <td>".$row['stud']."</td>
                    <td>".$row['firstname'].' '.$row['lastname']."</td>
                    <td>".$row['isbn']."</td>
                    <td>".$row['title']."</td>
                    <td><span class='label label-success'>Returned</span></td>
                </tr>
            ";
        }
    }

    function includeNotReturnedBooks() {
        include 'includes/conn.php';
        $sql_not_returned = "SELECT *, students.student_id AS stud, borrow.status AS barstat FROM borrow LEFT JOIN students ON students.id=borrow.student_id LEFT JOIN books ON books.id=borrow.book_id WHERE borrow.status = 0 ORDER BY date_borrow DESC";
        $query_not_returned = $conn->query($sql_not_returned);
        while($row = $query_not_returned->fetch_assoc()){
            echo "
                <tr>
                    <td>".date('M d, Y', strtotime($row['date_borrow']))."</td>
                    <td>".$row['stud']."</td>
                    <td>".$row['firstname'].' '.$row['lastname']."</td>
                    <td>".$row['isbn']."</td>
                    <td>".$row['title']."</td>
                    <td><span class='label label-danger'>Not Returned</span></td>
                </tr>
            ";
        }
    }
    function includeBookReports() {
        include 'includes/conn.php';
        $sql_dates = "SELECT DISTINCT DATE(date_borrow) AS borrow_date FROM borrow ORDER BY borrow_date DESC";
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
