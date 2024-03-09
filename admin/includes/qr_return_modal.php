<!-- Add SweetAlert CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css">

  <!-- Add jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Add SweetAlert JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

<div class="modal fade" id="addnew1">
    <div class="modal-dialog">
        <div class="modal-content">
          	<div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-label="Close">
              		<span aria-hidden="true">&times;</span></button>
            	
              <h4 class="modal-title"><b>Scan QR Code to get Student ID</b></h4>
                <div class="modal-body">
            <div class= "iframe-container">
            <iframe src="http://192.168.0.106" width="480" height="320" frameborder="0" scrolling="no"></iframe>
         </div>
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
			 <button type="button" class="btn btn-primary btn-flat" id="confirmButton1"><i class="fa fa-save"></i> Save</button>
            	</form>
          	</div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#confirmButton1').on('click', function() {
            var studentNumber = $('#student').val();
            var isbnArray = []; // Array to store ISBNs

            // Loop through all ISBN input fields and collect their values
            $('#addnew1 input[name="isbn[]"]').each(function() {
                isbnArray.push($(this).val());
            });

            // AJAX request to fetch book details
            $.ajax({
                url: 'qr_book_return_confirmation.php',
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
<style>
        /* Center the iframe horizontally */
        .iframe-container {
            display: flex;
            justify-content: center;
        }

        /* Optional: Adjust the size of the iframe */
        iframe {
            width: 480px;
            height: 480px;
        }
    </style>