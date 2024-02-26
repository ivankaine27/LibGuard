<?php
include 'includes/session.php';

if(isset($_POST['isbn'])) {
    $isbnArray = $_POST['isbn'];
    $responseArray = array(); // Array to store responses for each ISBN

    foreach($isbnArray as $isbn) {
        // Fetch book details from the database
        $sql = "SELECT title, author FROM books WHERE isbn = '$isbn'";
        $result = $conn->query($sql);

        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $title = $row['title'];
            $author = $row['author'];

            // Add book details to the response array
            $responseArray[] = array('isbn' => $isbn, 'title' => $title, 'author' => $author);
        } else {
            // Book details not found for the current ISBN
            $responseArray[] = array('isbn' => $isbn, 'error' => 'Book details not found');
        }
    }

    // Return book details as JSON response
    echo json_encode($responseArray);
} else {
    // ISBN parameter not provided
    echo json_encode(array('error' => 'ISBN parameter not provided'));
}
?>
