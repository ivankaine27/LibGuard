<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-maroon-gold sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>
        Student List
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Students</li>
        <li class="active">Student List</li>
      </ol>
    </section>
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <button id="downloadTransaction" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-download"></i> Download Transaction History of Selected Student/s</button>
            </div>
            <div class="box tools pull-right">
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th>Firstname</th>
                  <th>Lastname</th>
                  <th>Student ID</th>
                  <th>Email</th>
                  <th>Program</th>
                  <th>Photo</th>
                  <th>Select Student/s</th>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT *, students.id AS studid FROM students LEFT JOIN course ON course.id=students.course_id";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      $photo = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg';
                      echo "
                        <tr>
                          <td>".$row['firstname']."</td>
                          <td>".$row['lastname']."</td>
                          <td>".$row['student_id']."</td>
                          <td>".$row['email']."</td>
                          <td>".$row['code']."</td>
                          <td>
                            <img src='".$photo."' width='30px' height='30px'>
                            <a href='#edit_photo' data-toggle='modal' class='pull-right photo' data-id='".$row['studid']."'><span class='fa fa-edit'></span></a>
                          </td>
                          <td style='text-align: center;'>
                            <input type='checkbox' class='select-checkbox' data-id='".$row['studid']." ' style='width: 20px; height: 20px;'>
                          </td>
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
      </div>
    </section>   
  </div>
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/download-student.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
  $(document).ready(function() {
    $('#downloadTransaction').click(function() {
      var selectedStudents = [];
      $('.select-checkbox:checked').each(function() {
        selectedStudents.push($(this).data('id'));
      });

      if (selectedStudents.length > 0) {
        // Pass the selected students to the download page along with the page number
        window.location = 'download-student.php?students=' + JSON.stringify(selectedStudents) + '&page=1';
      } else {
        alert('Please select at least one student to download their transaction history.');
      }
    });
  });
</script>
</body>
</html>
