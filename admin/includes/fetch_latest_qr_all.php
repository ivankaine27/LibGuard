<?php
// Include the database connection file
include 'conn.php';

// Function to fetch the latest QR data from the database
function fetchStudentData() {
    global $conn;
    $selectQuery = "SELECT idNumber, FirstName, LastName, Course FROM data ORDER BY id DESC LIMIT 1";
    $selectResult = mysqli_query($conn, $selectQuery);

    // Check if data was fetched successfully
    if ($selectResult && mysqli_num_rows($selectResult) > 0) {
        $row = mysqli_fetch_assoc($selectResult);
        // Return the fetched data as an array
        return $row;
    } else {
        return null; // Return null if no data found
    }
}

// Call the function to fetch the latest QR data
$studentData = fetchStudentData();

// Return the latest QR data fields as JSON
echo json_encode($studentData);
?>
