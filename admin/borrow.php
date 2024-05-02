<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-maroon-gold sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Borrow Books
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Transaction</li>
        <li class="active">Borrow</li>
      </ol>
    </section>
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
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Borrow</a>
               </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Date Borrowed</th>
                  <th>Due Date</th>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>ISBN</th>
                  <th>Title</th>
                  <th>Status</th>
                  <th>Email Button</th>
                </thead>
                <tbody>
                  <?php
                    $department = isset($_GET['department']) ? $_GET['department'] : null;

                    $sql = "SELECT DISTINCT b.id, b.*, r.date_return AS return_date, students.student_id AS stud, students.firstname, students.email, students.lastname, books.isbn, books.title, books.author
                            FROM borrow b
                            LEFT JOIN returns r ON b.book_id = r.book_id AND b.student_id = r.student_id 
                            LEFT JOIN students ON students.id = b.student_id 
                            LEFT JOIN books ON books.id = b.book_id
                            WHERE b.status = 0";
                    
                    if ($department !== null) {
                        // Include the department filter when it's provided
                        $sql .= " WHERE b.department = '$department'";
                    }
                    $sql .= " ORDER BY b.date_borrow DESC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                        $not_returned = '<span class="label label-danger">not returned</span>';
                        $overdue = '<span class="label label-danger">OVERDUE!</span>';
                        $status = ($row['status']) ? '<span class="label label-success">returned</span>' : $not_returned;
                        $due_date = strtotime($row['due_date']);
                        $current_date = strtotime(date('d-m-Y'));
                        if ($due_date < $current_date) {
                          // If past due date, change status to "not returned"
                          $status = $overdue;
                          // Update the status in the database
                        }
                        echo "
                            <tr>
                                <td class='hidden'></td>
                                <td>".date('M d, Y', strtotime($row['date_borrow']))."</td>
                                <td>".date('M d, Y', strtotime($row['due_date']))."</td>
                                <td>".$row['stud']."</td>
                                <td>".$row['firstname'].' '.$row['lastname']."</td>
                                <td>".$row['isbn']."</td>
                                <td>".$row['title']."</td>
                                <td>".$status."</td>
                                <td>
                                <button class='btn btn-info btn-sm send-email-btn' data-email='".$row['email']."' data-firstname='".$row['firstname']."' data-title='".$row['title']."' data-due-date='".date('M d, Y', strtotime($row['due_date']))."' data-status='".$status."' data-penalty='".$row['penalty']."'>Email <i class='fa fa-send'></i></button></td>
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
<script>
$(function(){
  $(document).on('click', '#append', function(e){
    e.preventDefault();
    $('#append-div').append(
      '<div class="form-group"><label for="" class="col-sm-3 control-label">ISBN</label><div class="col-sm-9"><input type="text" class="form-control" name="isbn[]"></div></div>'
    );
  });
  $(document).on('click', '.send-email-btn', function(e){
            e.preventDefault();
            var email = $(this).data('email');
            var firstName = $(this).data('firstname');
            var title = $(this).data('title');
            var dueDate = $(this).data('due-date');
            var status = $(this).data('status');
            var penalty = $(this).data('penalty');
            sendEmail(email, firstName, title, dueDate, status, penalty);
        });
  function sendEmail(email, firstname, title, due_date, status, penalty) {
    $.ajax({
      url: 'send_email.php',
      type: 'POST',
      data: { 
        email: email,
        firstname: firstname,
        title: title,
        due_date: due_date,
        status: status,
        penalty: penalty
      },
      success: function(response) {
        alert('Email sent successfully!');
      },
      error: function(xhr, status, error) {
        alert('Error sending email: ' + error);
      }
  });
}

});
</script>
</body>
</html>
