<?php
// Include your database connection file here
 include 'includes/session.php';

// Initialize the array to store the data
$data = array();

// Execute SQL query to fetch book transactions by publish year
$query = "SELECT YEAR(books.publish_date) AS publish_year, COUNT(*) AS total_transactions
          FROM borrow
          LEFT JOIN books ON borrow.book_id = books.id";

if ($selected_publish !== null) {
    $publishYears = explode(',', $selected_publish);
    $publishYearsString = implode(',', $publishYears);
    $query .= " WHERE YEAR(books.publish_date) IN ($publishYearsString)";
}

// Add the date range condition
if ($startDate && $endDate) {
    $query .= " AND borrow.date_borrow BETWEEN '$startDate' AND '$endDate'";
}

$query .= "GROUP BY publish_year";
$result = $conn->query($query);

// Check if query executed successfully
if ($result) {
    // Fetch associative array
    while ($row = $result->fetch_assoc()) {
        // Append data to the array
        $data[] = $row;
    }
    // Free result set
    $result->free();
}

// Close the database connection
$conn->close();

// Return data in JSON format
header('Content-Type: application/json');
echo json_encode($data);
?>
