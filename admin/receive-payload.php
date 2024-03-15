<?php
// Include the database connection file
include 'includes/conn.php';

// Check if any data was received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = file_get_contents('php://input');
    echo "Raw POST data: " . $postData . "<br>";

    // Parse the URL-encoded data
    parse_str($postData, $parsedData);

    // Check if the 'resultQR' parameter exists in the parsed data
    if (isset($parsedData['resultQR'])) {
        // Retrieve the value of 'resultQR' parameter
        $resultQR = $parsedData['resultQR'];
        echo "Received QR data: " . $resultQR . "<br>";

        // Extract ID number from the received data
        preg_match('/IDNo: (\d+)/', $resultQR, $matches);
        $idNumber = isset($matches[1]) ? $matches[1] : '';

        if (!empty($idNumber)) {
            // Escape the data to prevent SQL injection
            $escapedIdNumber = mysqli_real_escape_string($conn, $idNumber);

            // Insert the ID number into the database table 'data' and column 'qrData'
            $query = "INSERT INTO data (qrData) VALUES ('$escapedIdNumber')";
            $insertResult = mysqli_query($conn, $query);

            // Check if the insertion was successful
            if ($insertResult) {
                // Echo success message
                echo "ID number inserted successfully: " . $idNumber . "<br>";

                // Send the QR data to the student form and close the modal
                echo "<script>
                        var qrData = '" . $idNumber . "';
                        // Send QR data to the student form
                        $('#student').val(qrData);
                        // Close the scanqr modal
                        $('#scanqr').modal('hide');
                      </script>";
            } else {
                echo "Error: Unable to insert ID number into the database.<br>";
            }
        } else {
            echo "Error: Unable to extract ID number from the received data.<br>";
        }
    } else {
        // If 'resultQR' parameter is not found
        echo "Error: 'resultQR' parameter is missing in the POST request.<br>";
    }
}

// Function to fetch data from the database
function fetchData() {
    global $conn;
    $selectQuery = "SELECT qrData FROM data";
    $selectResult = mysqli_query($conn, $selectQuery);

    // Check if data was fetched successfully
    if ($selectResult && mysqli_num_rows($selectResult) > 0) {
        // Display the retrieved data in an HTML table
        echo "<table id='qrDataTable' border='1'>";
        echo "<tr><th>#</th><th>Inserted ID number</th></tr>";
        $counter = 1; // Initialize counter
        while ($row = mysqli_fetch_assoc($selectResult)) {
            echo "<tr><td>" . $counter++ . "</td><td>" . $row['qrData'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "Error: No data found in the database.<br>";
    }
}

// Call the fetch function
fetchData();
?>
