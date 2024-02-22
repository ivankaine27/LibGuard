<?php include 'includes/session.php'; ?>
<?php
    if(!isset($_SESSION['student']) || trim($_SESSION['student']) == ''){
        header('Location: index.php'); // Corrected redirection syntax
    }

    $stuid = $student['id'];
    $sql = "SELECT * FROM borrow LEFT JOIN books ON books.id=borrow.book_id WHERE student_id = '$stuid' ORDER BY date_borrow DESC";
    $action = '';
    if(isset($_GET['action']) && $_GET['action'] == 'return'){
        $sql = "SELECT * FROM returns LEFT JOIN books ON books.id=returns.book_id WHERE student_id = '$stuid' ORDER BY date_return DESC";
        $action = $_GET['action'];
    }
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
                                <div class="pull-right">
                                    <select class="form-control input-sm" id="transelect">
                                        <option value="borrow" <?php echo ($action == '') ? 'selected' : ''; ?>>Borrow</option>
                                        <option value="return" <?php echo ($action == 'return') ? 'selected' : ''; ?>>Return</option>
                                    </select>
                                </div>
                            </div>
                            <div class="box-body">
                                <table class="table table-bordered table-striped" id="example1">
                                    <thead>
                                        <th class="hidden"></th>
                                        <th>Date Borrowed</th>
                                        <th>Date Returned</th> <!-- Added Returned Date column -->
                                        <th>ISBN</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                    </thead>
                                    <tbody>
                                    <?php
                                        $query = $conn->query($sql);
                                        while($row = $query->fetch_assoc()){
                                            $date_borrow = (isset($_GET['action'])) ? 'date_return' : 'date_borrow';
                                            $date_return = (isset($_GET['action'])) ? 'date_return' : ''; // Fetch returned date
                                            echo "
                                                <tr>
                                                    <td class='hidden'></td>
                                                    <td>".date('M d, Y', strtotime($row[$date_borrow]))."</td>
                                                    <td>".($date_return != '' ? date('M d, Y', strtotime($row[$date_return])) : '')."</td> <!-- Display returned date if available -->
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
<script>
    $('#transelect').on('change', function(){
        var action = $(this).val();
        if(action == 'borrow'){
            window.location = 'transaction.php';
        }
        else{
            window.location = 'transaction.php?action='+action;
        }
    });
</script>
</body>
</html>
