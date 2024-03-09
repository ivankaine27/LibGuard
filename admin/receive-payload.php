<div class= "iframe-container">
            <iframe src="http://192.168.0.131" width="480" height="320" frameborder="0" scrolling="no"></iframe>
         </div>


<?php
   include 'includes/session.php';
// Check if the payload data is sent using the POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the payload parameter is set in the POST data
    if (isset($_POST["postData"])) {
        // Retrieve the payload data
        $qrData = $_POST["postData"];
        $sql = "INSERT INTO data (qrData) VALUES ('$qrData')";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Category added successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;

        // Process the payload data as needed
        // For example, you can store it in a database or perform other actions

        // Print a response to acknowledge that the payload was received
        echo "Payload received successfully: " . $qrData;
    }
} else {
    // If the request method is not POST, print an error message
    echo "Error: Only POST requests are allowed";
}
}
?>
<style>
        /* Center the iframe horizontally */
        .iframe-container {
            display: flex;
            justify-content: center;
        }

        /* Optional: Adjust the size of the iframe */
        iframe {
            width: 480px;
            height: 320px;
        }
</style>