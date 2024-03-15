<?php
// Include the database connection file
include 'conn.php';

// Function to fetch the latest QR data from the database
function fetchLatestidNumber() {
    global $conn;
    $selectQuery = "SELECT idNumber FROM data ORDER BY id DESC LIMIT 1";
    $selectResult = mysqli_query($conn, $selectQuery);

    // Check if data was fetched successfully
    if ($selectResult && mysqli_num_rows($selectResult) > 0) {
        $row = mysqli_fetch_assoc($selectResult);
        return $row['idNumber']; // Return the latest QR data
    } else {
        return null; // Return null if no data found
    }
}

// Call the function to fetch the latest QR data
$latestidNumber = fetchLatestidNumber();

// Return the latest QR data as JSON
echo json_encode(array('idNumber' => $latestidNumber));
?>
