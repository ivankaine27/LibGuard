<?php
include 'includes/session.php';

if(isset($_POST['isbn'])) {
    $isbnArray = $_POST['isbn'];
    $responseArray = array(); // Array to store responses for each ISBN
    $penaltyTotal = 0; // Initialize penalty total
    foreach($isbnArray as $isbn) {
        // Fetch book details from the database
        $sql = "SELECT b.id as book_id, b.title, b.author, br.due_date 
                FROM books b 
                INNER JOIN borrow br ON b.id = br.book_id 
                WHERE b.isbn = '$isbn' AND br.status = 0";
        $result = $conn->query($sql);

        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $title = $row['title'];
            $author = $row['author'];
            $dueDate = $row['due_date']; // Assuming due_date is the correct column name
            // Add book details and penalty to the response array
// Calculate the penalty here
$returned_date = date('Y-m-d');
$days_late = (strtotime($returned_date) - strtotime($dueDate)) / (60 * 60 * 24); // Calculate days late
if ($days_late <= 0) {
    // If returned_date is earlier than or equal to dueDate, penalty is 0
    $penalty = 0;
} else {
    // Penalty: 10 PHP per day late
    $penalty = 5 * $days_late;
}
$penaltyTotal += $penalty; // Accumulate penalty total

            $penalty_php = number_format($penaltyTotal, 2);

            $responseArray[] = array(
                'isbn' => $isbn,
                'title' => $title,
                'author' => $author,
                'penalty' => $penalty_php
            );
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
