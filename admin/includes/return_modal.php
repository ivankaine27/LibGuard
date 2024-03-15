<!-- Add SweetAlert CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css">

  <!-- Add jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Add SweetAlert JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
          	<div class="modal-header">
              <a href="#scanqr" data-toggle="modal" class="btn btn-primary pull-right btn-sm btn-flat"><i class="fa fa-camera"></i>  Scan QR Code</a>
            	<h4 class="modal-title"><b>Return Books</b></h4>
          	</div>
          	<div class="modal-body">
            	<form class="form-horizontal" method="POST" action="return_add.php">
          		  <div class="form-group">
                  	<label for="student" class="col-sm-3 control-label">Student ID</label>

                  	<div class="col-sm-9">
                    	<input type="text" class="form-control" id="student" name="student" required>
                  	</div>
                </div>
                <div class="form-group">
                    <label for="isbn" class="col-sm-3 control-label">ISBN</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="isbn" name="isbn[]" required>
                    </div>
                </div>
                <span id="append-div"></span>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3">
                      <button class="btn btn-primary btn-xs btn-flat" id="append"><i class="fa fa-plus"></i> Book Field</button>
                    </div>
                </div>
          	</div>
          	<div class="modal-footer">
            	<button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
             <!-- Change type to button and add name="confirm" -->
			 <button type="button" class="btn btn-primary btn-flat" id="confirmButton"><i class="fa fa-save"></i> Save</button>
            	</form>
          	</div>
        </div>
    </div>
    <?php include 'includes/qr_modal.php'; ?>
</div>
<script>
    $(document).ready(function() {

                // Function to fetch the latest QR data
    function fetchLatestidNumber() {
        $.ajax({
            url: 'includes/fetch_latest_qr.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.idNumber !== null) {
                    // Update the student ID input field with the latest QR data
                    $('#student').val(response.idNumber);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching latest ID Number:', error);
            }
        });
    }

// When the "scanqr" modal is hidden
$('#scanqr').on('hidden.bs.modal', function() {
    // Fetch the latest QR data
    fetchLatestidNumber();
});
        $('#confirmButton').on('click', function() {
            var studentNumber = $('#student').val();
            var isbnArray = []; // Array to store ISBNs

            // Loop through all ISBN input fields and collect their values
            $('input[name="isbn[]"]').each(function() {
                isbnArray.push($(this).val());
            });

            // AJAX request to fetch book details
            $.ajax({
                url: 'book_return_confirmation.php',
                method: 'POST',
                data: { isbn: isbnArray }, // Send array of ISBNs
                dataType: 'json',
                success: function(response) {
                    // Display SweetAlert confirmation dialog with book details
                    if(response.error) {
                        swal('Error', response.error, 'error');
                    } else {
                        var confirmationText = 'Are you sure you want to proceed with returning the following books?\n\nStudent Number: ' + studentNumber + '\n';

                        // Add details of each book to the confirmation text
                        $.each(response, function(index, book) {
                            confirmationText += '\nISBN: ' + book.isbn + '\nTitle: ' + book.title + '\nAuthor: ' + book.author + '\nPenalty: PHP ' + book.penalty + '\n';
                        });

                        swal({
                            title: 'Confirmation',
                            text: confirmationText,
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        }).then((willProceed) => {
                            if (willProceed) {
                                // If user confirms, submit the form with the name "add"
                                $('form').append('<input type="hidden" name="add">').submit();
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    swal('Error', 'Error fetching book details', 'error');
                }
            });
        });
    });
</script>
