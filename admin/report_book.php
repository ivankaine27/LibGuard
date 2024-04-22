<?php include 'includes/session.php'; ?>
<?php 
  include 'includes/timezone.php'; 
  $today = date('Y-m-d');
  $year = date('Y');
  if(isset($_GET['year'])){
    $year = $_GET['year'];
  }
?>
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
        Generate Report
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Generate Report</li>
      </ol>
          <!-- Button for specifying filters -->
    </section>

    <!-- Main content -->
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
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box" style="background-color: rgba(0, 200, 250, 0.7)">
            <div class="inner">
              <!-- <?php
                $sql = "SELECT * FROM books";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
              ?> -->

              <h4><strong>Published Year</strong></h4>
              <h4> ...</h4>
            </div>
            <div class="icon">
              <i class="fa fa-book"></i>
            </div>
            <a href="#" id="openPublishModal" class="small-box-footer">Click to Download <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box" style="background-color: rgba(255, 170, 0, 0.7)">
            <div class="inner">
              <!-- <?php
                $sql = "SELECT * FROM returns WHERE date_return = '$today'";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
              ?> -->
             
              <h4><strong>Book Category</strong></h4>
              <h4> ...</h4>
            </div>
            <div class="icon">
              <i class="fa fa-list-alt"></i>
            </div>
            <a href="#" id="openCategoryModal" class="small-box-footer">Click to Download <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
          <!-- small box -->
          <div class="small-box" style="background-color: rgba(255, 0, 0, 0.7)">
            <div class="inner">
              <!-- <?php
                $sql = "SELECT * FROM borrow WHERE date_borrow = '$today'";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
              ?> -->

              <h4><strong>Course</strong></h4>
              <h4> ...</h4>
            </div>
            <div class="icon">
              <i class="fa fa-download"></i>
            </div>
            <a href="#" id="openCourseModal" class="small-box-footer">Generate Report <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box" style="background-color: rgba(0, 0, 0, 0.3);">
              <div class="inner">
                <!-- <?php
                  $sql = "SELECT * FROM books";
                  $query = $conn->query($sql);

                  echo "<h3>".$query->num_rows."</h3>";
                ?>
           -->
           <h4><strong>Book Transaction</strong></h4>
           <h4> ...</h4>
              </div>
              <div class="icon">
                <i class="fa fa-bookmark"></i>
              </div>
              <a href="#" id="openCalendarModal" class="small-box-footer">Generate Report <i class="fa fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>
        
        <!-- /.row -->
        <div class="row">
          <div class="col-xs-12">
            <div class="box">
              <div class="box-header with-border">
                <h3 class="box-title">Monthly Transaction Report</h3>
                <div class="box-tools pull-right">
                  <form class="form-inline">
                    <div class="form-group">
                      <label>Select Year: </label>
                      <select class="form-control input-sm" id="select_year">
                        <?php
                          for($i=2015; $i<=2065; $i++){
                            $selected = ($i==$year)?'selected':'';
                            echo "
                              <option value='".$i."' ".$selected.">".$i."</option>
                            ";
                          }
                        ?>
                      </select>
                    </div>
                  </form>
                </div>
              </div>
              <div class="box-body">
                <div class="chart">
                  <br>
                  <div id="legend" class="text-center"></div>
                  <canvas id="barChart" style="height:350px"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

      </section>
      <!-- right col -->
    </div>
    <?php include 'includes/footer.php'; ?>

  </div>
  <!-- ./wrapper -->

  <!-- Chart Data -->
  <?php
    $and = 'AND YEAR(date) = '.$year;
    $months = array();
    $return = array();
    $borrow = array();
    for( $m = 1; $m <= 12; $m++ ) {
      $sql = "SELECT * FROM returns WHERE MONTH(date_return) = '$m' AND YEAR(date_return) = '$year'";
      $rquery = $conn->query($sql);
      array_push($return, $rquery->num_rows);

      $sql = "SELECT * FROM borrow WHERE MONTH(date_borrow) = '$m' AND YEAR(date_borrow) = '$year'";
      $bquery = $conn->query($sql);
      array_push($borrow, $bquery->num_rows);

      $num = str_pad( $m, 2, 0, STR_PAD_LEFT );
      $month =  date('M', mktime(0, 0, 0, $m, 1));
      array_push($months, $month);
    }

    $months = json_encode($months);
    $return = json_encode($return);
    $borrow = json_encode($borrow);

  ?>
  <!-- End Chart Data -->
  <?php include 'includes/scripts.php'; ?>
  <script>
  $(function(){
    var barChartCanvas = $('#barChart').get(0).getContext('2d')
    var barChart = new Chart(barChartCanvas)
    var barChartData = {
      labels  : <?php echo $months; ?>,
      datasets: [
        {
          label               : 'Borrow',
          fillColor           : 'rgba(210, 214, 222, 1)',
          strokeColor         : 'rgba(210, 214, 222, 1)',
          pointColor          : 'rgba(210, 214, 222, 1)',
          pointStrokeColor    : '#c1c7d1',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(220,220,220,1)',
          data                : <?php echo $borrow; ?>
        },
        {
          label               : 'Return',
          fillColor           : 'rgba(60,141,188,0.9)',
          strokeColor         : 'rgba(60,141,188,0.8)',
          pointColor          : '#3b8bba',
          pointStrokeColor    : 'rgba(60,141,188,1)',
          pointHighlightFill  : '#fff',
          pointHighlightStroke: 'rgba(60,141,188,1)',
          data                : <?php echo $return; ?>
        }
      ]
    }
    barChartData.datasets[1].fillColor   = '#00a65a'
    barChartData.datasets[1].strokeColor = '#00a65a'
    barChartData.datasets[1].pointColor  = '#00a65a'
    var barChartOptions                  = {
      //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
      scaleBeginAtZero        : true,
      //Boolean - Whether grid lines are shown across the chart
      scaleShowGridLines      : true,
      //String - Colour of the grid lines
      scaleGridLineColor      : 'rgba(0,0,0,.05)',
      //Number - Width of the grid lines
      scaleGridLineWidth      : 1,
      //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: true,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines  : true,
      //Boolean - If there is a stroke on each bar
      barShowStroke           : true,
      //Number - Pixel width of the bar stroke
      barStrokeWidth          : 2,
      //Number - Spacing between each of the X value sets
      barValueSpacing         : 5,
      //Number - Spacing between data sets within X values
      barDatasetSpacing       : 1,
      //String - A legend template
      legendTemplate          : '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
      //Boolean - whether to make the chart responsive
      responsive              : true,
      maintainAspectRatio     : true
    }

    barChartOptions.datasetFill = false
    var myChart = barChart.Bar(barChartData, barChartOptions)
    document.getElementById('legend').innerHTML = myChart.generateLegend();
  });
  </script>
  <script>
  $(function(){
    $('#select_year').change(function(){
      window.location.href = 'home.php?year='+$(this).val();
    });
  });
  </script>
  <!-- Import Bootstrap and other libraries if not already imported -->
<!-- Include Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

<!-- Include Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

<!-- Include jQuery and Bootstrap JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<!-- Include Bootstrap Datepicker JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
  $(document).ready(function() {
    // Initialize Bootstrap Datepicker
    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true,
    });
  });
</script>

  <!-- Import your calendar modal -->
  <?php include 'includes/calendar_modal.php'; ?>
  <?php include 'includes/modals/course_modal.php'; ?>
  <?php include 'includes/modals/category_modal.php'; ?>
  <?php include 'includes/modals/publish_modal.php'; ?>
  <?php include 'includes/modals/return_modal.php'; ?>
  <!-- Add the following script to handle the click event -->
  <script>
      $(document).ready(function() {
          // Handle click event for the "Click to Download" button
          $('#openCalendarModal').click(function(e) {
              e.preventDefault(); // Prevent default link behavior

              // Show the modal
              $('#dateRangePickerModal').modal('show');
          });
          $('#openCourseModal').click(function(e) {
              e.preventDefault(); // Prevent default link behavior

              // Show the modal
              $('#courseModal').modal('show');
          });
          $('#openCategoryModal').click(function(e) {
              e.preventDefault(); // Prevent default link behavior

              // Show the modal
              $('#categoryModal').modal('show');
          });
          $('#openPublishModal').click(function(e) {
              e.preventDefault(); // Prevent default link behavior
              // Show the modal
              $('#publishModal').modal('show');
          });
          $('#openReturnModal').click(function(e) {
              e.preventDefault(); // Prevent default link behavior

              // Show the modal
              $('#returnModal').modal('show');
          });
      });
  </script>

</body>
</html>
<style>
  .box {
    border-radius: 10px; /* Adjust the value as needed */
}
  .small-box {
    border-radius: 10px; /* Adjust the value as needed */
}
</style>