<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation Dialog with SweetAlert</title>
  <!-- Add SweetAlert CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.css">
  <!-- Add jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<!-- Add -->
<div class="modal fade" id="scanqr">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Scan QR Code to get Student ID</b></h4>
            </div>
            <div class="modal-body">
                <div class= "iframe-container">
                    <iframe src="http://192.168.0.112" width="480" height="320" frameborder="0" scrolling="no"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>


</body>
</html>
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
