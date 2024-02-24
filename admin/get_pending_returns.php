<?php
include 'includes/session.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];
    
    // Select book_ids from borrow table where student_id matches and status is 0
    $sql = "SELECT book_id FROM borrow WHERE student_id = '$id' AND status = 0";
    $query = $conn->query($sql);
    
    $rows = array();
    
    // Fetch book IDs and store them in an array
    while($row = $query->fetch_assoc()) {
        $book_id = $row['book_id'];
        
        // Select title from books table where book_id matches
        $sql_title = "SELECT title FROM books WHERE id = '$book_id'";
        $query_title = $conn->query($sql_title);
        
        // Fetch title of the book and add it to the result array
        while($row_title = $query_title->fetch_assoc()) {
            $rows[] = $row_title;
        }
    }
    
    echo json_encode($rows);
}
?>
