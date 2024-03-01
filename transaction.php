<?php include 'includes/session.php'; ?>
<?php
    if(!isset($_SESSION['student']) || trim($_SESSION['student']) == ''){
        header('Location: index.php'); // Corrected redirection syntax
    }

    $stuid = $student['id'];
    // Query to fetch distinct borrow transactions along with book details
    $sql = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, books.isbn, books.title, books.author
            FROM borrow b
            LEFT JOIN returns r ON b.book_id = r.book_id
            LEFT JOIN books ON books.id = b.book_id
            WHERE b.student_id = '$stuid'
            ORDER BY b.date_borrow DESC";
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-maroon-gold layout-top-nav">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <div class="content-wrapper">
        <div class="container">
            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">TRANSACTIONS</h3>
                            </div>
                            <div class="box-body">
                                <table class="table table-bordered table-striped" id="example1">
                                    <thead>
                                        <th>Date Borrowed</th>
                                        <th>Date Returned</th>
                                        <th>ISBN</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $query = $conn->query($sql);
                                        while($row = $query->fetch_assoc()){
                                            echo "
                                                <tr>
                                                    <td>".date('M d, Y', strtotime($row['date_borrow']))."</td>
                                                    <td>".($row['return_date'] ? date('M d, Y', strtotime($row['return_date'])) : "Not Returned Yet")."</td>
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
            </section>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
