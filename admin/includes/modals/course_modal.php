<div class="modal fade" id="courseModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Select Date Range</b></h4>
            </div>
            <form class="form-horizontal" id="dateRangeForm" action="process_form.php" method="post">
            <div class="modal-body">
                
                    <?php
                        $sql = "SELECT title, id from course;";
                        $query = $conn->query($sql);

                        while($row = $query->fetch_assoc()){
                            echo "
                                <input type='checkbox' name='selected_courses[]' class='select-checkbox' data-id='".$row['id']."' style='width: 20px; height: 20px;' id='".$row['id']."'>
                                <label for='".$row['id']."'>".$row['title']."</label>
                                <br>
                            ";
                        }
                    ?>   
                    <div class="form-group">
                        <label for="startDate" class="col-sm-3 control-label">Start Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control datepicker" id="startDateCourse" name="startDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="endDate" class="col-sm-3 control-label">End Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control datepicker" id="endDateCourse" name="endDate" required>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat" id="saveCourse"><i class="fa fa-save"></i> Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // When the "Save" button in the modal is clicked
        $('#saveCourse').click(function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Get the selected checkbox values
            var selectedCourses = $('.select-checkbox:checked').map(function() {
                return this.getAttribute('data-id');
            }).get();
            var startDate = $('#startDateCourse').val();
            var endDate = $('#endDateCourse').val();
            // Convert the selectedCourses array to a comma-separated string
            var selectedCoursesString = selectedCourses.join(',');
            // Construct the URL with the selected course IDs
            var url = 'course_report.php?selected_courses=' + selectedCoursesString + '&startDate=' + startDate + '&endDate=' + endDate;
            // Redirect the user to the course_report.php page with the selected course IDs
            window.location.href = url;

            // Hide the modal
            $('#courseModal').modal('hide');
        });
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
</script>

