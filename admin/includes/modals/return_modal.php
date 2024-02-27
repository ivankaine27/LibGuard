<!-- Add -->
<div class="modal fade" id="returnModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><b>Select Date Range</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="dateRangeForm">
                <div class="form-group">
                        <label for="category" class="col-sm-3 control-label">Category</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control textfield" id="category" name="category" required>
                        </div>
                    </div>
                <!-- <div class="form-group">
                    <label for="endDate" class="col-sm-3 control-label">End Date</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control datepicker" id="startDateReturn" name="startDate" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="endDate" class="col-sm-3 control-label">End Date</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control datepicker" id="endDateReturn" name="endDate" required>
                    </div>
                </div> -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat" id="saveReturn"><i class="fa fa-save"></i> Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // When the "Save" button in the modal is clicked
    $('#saveReturn').click(function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Get the selected start and end dates
        var category = $('#category').val();
        // var endDate = $('#endDateBorrow').val();

        // Construct the URL with the selected date range parameters
        var url = 'return.php?category_id=' + category;

        // Redirect the user to the download-book.php page with the selected date range
        window.location.href = url;

        // Hide the modal
        $('#returnModal').modal('hide');
    });
});
</script>

