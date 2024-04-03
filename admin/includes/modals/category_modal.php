<div class="modal fade" id="categoryModal">
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
                        $sql = "SELECT name, id from category;";
                        $query = $conn->query($sql);

                        while($row = $query->fetch_assoc()){
                            echo "
                                <input type='checkbox' name='selected_courses[]' class='select-checkbox' data-id='".$row['id']."' style='width: 20px; height: 20px;' id='".$row['id']."'>
                                <label for='".$row['id']."'>".$row['name']."</label>
                                <br>
                            ";
                        }
                    ?>   
                    <div class="form-group">
                        <label for="startDate" class="col-sm-3 control-label">Start Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control datepicker" id="startDateCategory" name="startDate" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="endDate" class="col-sm-3 control-label">End Date</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control datepicker" id="endDateCategory" name="endDate" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input books_borrowed_and_returned" type="checkbox" value="" id="books_borrowed_and_returned" name="books_borrowed_and_returned">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Books Borrowed and Returned
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input pending_book_returns" type="checkbox" value="" id="pending_book_returns" name="pending_book_returns">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Pending Book Returns
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input contrast_books_returned_and_pending_returns" type="checkbox" value="" id="contrast_books_returned_and_pending_returns" name="contrast_books_returned_and_pending_returns">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Contrast Books Returned and Pending Returns
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input all_transaction_history" type="checkbox" value="" id="all_transaction_history" name="all_transaction_history">
                                <label class="form-check-label" for="flexCheckDefault">
                                    All Transaction History
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input rankings_per_total_transaction" type="checkbox" value="" id="rankings_per_total_transaction" name="rankings_per_total_transaction">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Rankings per Total Transaction
                                </label>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat" id="saveCategory"><i class="fa fa-save"></i> Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        // When the "Save" button in the modal is clicked
        $('#saveCategory').click(function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Get the selected checkbox values
            var selectedCategory = $('.select-checkbox:checked').map(function() {
                return this.getAttribute('data-id');
            }).get();

            var booksBorrowedAndReturned = $('.books_borrowed_and_returned:checked').length > 0 ? '1' : '0';
            var pendingBookReturns = $('.pending_book_returns:checked').length > 0 ? '1' : '0';
            var contrastBooksReturnedAndPendingReturns = $('.contrast_books_returned_and_pending_returns:checked').length > 0 ? '1' : '0';
            var allTransactionHistory = $('.all_transaction_history:checked').length > 0 ? '1' : '0';
            var rankingsPerTotalTransaction = $('.rankings_per_total_transaction:checked').length > 0 ? '1' : '0';

            var startDate = $('#startDateCategory').val();
            var endDate = $('#endDateCategory').val();
            // Convert the selectedCourses array to a comma-separated string
            var selectedCategoryString = selectedCategory.join(',');
            // Construct the URL with the selected course IDs
            var url = 'category_report.php?selected_category=' + selectedCategoryString + 
                '&startDate=' + startDate + 
                '&endDate=' + endDate +
                '&books_borrowed_and_returned=' + booksBorrowedAndReturned +
                '&pending_book_returns=' + pendingBookReturns +
                '&contrast_books_returned_and_pending_returns=' + contrastBooksReturnedAndPendingReturns +
                '&all_transaction_history=' + allTransactionHistory +
                '&rankings_per_total_transaction=' + rankingsPerTotalTransaction;
                
            // Redirect the user to the course_report.php page with the selected course IDs
            window.location.href = url;

            // Hide the modal
            $('#categoryModal').modal('hide');
        });
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
</script>

