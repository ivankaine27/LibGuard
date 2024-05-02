<?php
// Include your database connection file
include 'conn.php';

// Check if ISBN is sent via POST request
if(isset($_POST['isbn'])) {
    // Sanitize and store the received ISBN
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);

    // Query to retrieve book details based on ISBN
    $query = "SELECT * FROM books WHERE isbn = '$isbn'";
    $result = mysqli_query($conn, $query);

    // Check if any rows are returned
    if(mysqli_num_rows($result) > 0) {
        // Fetch book details from the result set
        $book = mysqli_fetch_assoc($result);

        // Prepare book details to send back as JSON response
        $response = array(
            'title' => $book['title'],
            'author' => $book['author'],
            // Add more book details as needed
        );

        // Send book details as JSON response
        echo json_encode($response);
    } else {
        // If no book is found with the given ISBN, send an error response
        $error = "Book not found with the given ISBN.";
        echo json_encode(array('error' => $error));
    }
} else {
    // If ISBN is not sent via POST request, send an error response
    $error = "ISBN not provided.";
    echo json_encode(array('error' => $error));
}
?>
