<?php include 'includes/session.php'; ?>
<?php
    $where = '';
    if(isset($_GET['category'])){
        $catid = $_GET['category'];
        $where = 'AND b.category_id = '.$catid;
    }
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-maroon-gold layout-top-nav" style="background-image: url('images/logos/15.png'); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: rgba(255, 255, 255, 0.5);">

<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <div class="content-wrapper">
        <div class="container">
            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-sm-12 col-sm-offset-0">
                        <?php
                            if(isset($_SESSION['error'])){
                                echo "
                                    <div class='alert alert-danger'>
                                        ".$_SESSION['error']."
                                    </div>
                                ";
                                unset($_SESSION['error']);
                            }
                        ?>
                        <div class="box">
                            <div class="box-header with-border">
                                <div class="input-group">
                                    <input type="text" class="form-control input-lg" id="searchBox" placeholder="Search for ISBN, Title or Author">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-flat btn-lg"><i class="fa fa-search"></i> </button>
                                    </span>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="input-group col-sm-5 pull-right">
                                    <span class="input-group-addon">Category:</span>
                                    <select class="form-control" id="catlist">
                                        <option value=0>ALL</option>
                                        <?php
                                            $sql = "SELECT * FROM category";
                                            $query = $conn->query($sql);
                                            while($catrow = $query->fetch_assoc()){
                                                $selected = ($catid == $catrow['id']) ? " selected" : "";
                                                echo "
                                                    <option value='".$catrow['id']."' ".$selected.">".$catrow['name']."</option>
                                                ";
                                            }
                                        ?>
                                    </select>
                                </div>
                                <table class="table table-bordered table-striped" id="booklist">
                                    <thead>
                                        <th>ISBN</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Publisher</th> <!-- Added Publisher column -->
                                        <th>Publish Date</th> <!-- Added Publish Date column -->
                                        <th>Shelf Number</th>
                                        <th>Shelf Row</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Expected Return Date</th> <!-- Added column -->
                                    </thead>
                                    <tbody>
                                    <?php
                                        $sql = "SELECT b.*, br.due_Date
                                                FROM books b
                                                LEFT JOIN borrow br ON b.id = br.book_id
                                                WHERE 1 $where"; // Retrieve all books
                                        $query = $conn->query($sql);
                                        while($row = $query->fetch_assoc()){
                                            $status = ($row['status'] == 0) ? '<span class="label label-success">available</span>' : '<span class="label label-danger">not available</span>';
                                            $return_date = ($row['status'] == 1 && $row['due_Date'] != null) ? $row['due_Date'] : 'N/A'; // Display due date if not available
                                            echo "
                                                <tr>
                                                    <td>".$row['isbn']."</td>
                                                    <td>".$row['title']."</td>
                                                    <td>".$row['author']."</td>
                                                    <td>".$row['publisher']."</td> <!-- Display Publisher -->
                                                    <td>".$row['publish_date']."</td> <!-- Display Publish Date -->
                                                    <td>".$row['shelf_number']."</td>
                                                    <td>".$row['shelf_row']."</td>
                                                    <td>".$row['quantity']."</td>
                                                    <td>".$status."</td>
                                                    <td>".$return_date."</td> <!-- Display due date or N/A -->
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
$(function(){
    $('#catlist').on('change', function(){
        if($(this).val() == 0){
            window.location = 'index.php';
        }
        else{
            window.location = 'index.php?category='+$(this).val();
        }
    });
});
</script>
</body>
</html>
