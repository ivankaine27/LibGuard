<div class="modal fade" id="publishModal">
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
                        $sql = "SELECT DISTINCT YEAR(publish_date) AS publish_year FROM books ORDER BY publish_year ASC;";
                        $query = $conn->query($sql);

                        while ($row = $query->fetch_assoc()) {
                            echo "
                                <input type='checkbox' name='selected_years[]' class='select-checkbox' data-year='".$row['publish_year']."' style='width: 20px; height: 20px;' id='".$row['publish_year']."'>
                                <label for='".$row['publish_year']."'>".$row['publish_year']."</label>
                                <br>
                            ";
                        }
                    ?>  
                    <div class="form-group">
                        <label for="startDate" class="col-sm-3 control-label">Start Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control datepicker" id="startDatePublish" name="startDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="endDate" class="col-sm-3 control-label">End Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control datepicker" id="endDatePublish" name="endDate" required>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat" id="savePublish"><i class="fa fa-save"></i> Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // When the "Save" button in the modal is clicked
        $('#savePublish').click(function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Get the selected checkbox values
            var selectedPublish = $('.select-checkbox:checked').map(function() {
                return this.getAttribute('id');
            }).get();
            var startDate = $('#startDatePublish').val();
            var endDate = $('#endDatePublish').val();
            // Convert the selectedCourses array to a comma-separated string
            var selectedPublishString = selectedPublish.join(',');
            // Construct the URL with the selected course IDs
            var url = 'publish_report.php?selected_publish=' + selectedPublishString + '&startDate=' + startDate + '&endDate=' + endDate;
            // Redirect the user to the course_report.php page with the selected course IDs
            window.location.href = url;

            // Hide the modal
            $('#publishModal').modal('hide');
        });
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
</script>
