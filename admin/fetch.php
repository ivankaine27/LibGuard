<?php
// Assuming you have already established a database connection and $result holds the query result.

$data = array();

while ($row = $result->fetch_assoc()) {
    // Add transaction data to the $data array for JSON encoding
    $transaction = array(
        'date_borrow' => date('M d, Y', strtotime($row['date_borrow'])),
        'date_return' => $row['date_return'] ? date('M d, Y', strtotime($row['date_return'])) : "",
        'isbn' => $row['isbn'],
        'title' => $row['title'],
        'author' => $row['author']
    );
    $data[] = $transaction;
    
    // Echo HTML table row for each transaction
    echo "
        <tr>
            <td class='hidden'></td>
            <td>".$transaction['date_borrow']."</td>
            <td>".$transaction['date_return']."</td>
            <td>".$transaction['isbn']."</td>
            <td>".$transaction['title']."</td>
            <td>".$transaction['author']."</td>
        </tr>
    ";
}

// Encode $data array as JSON
$json_data = json_encode($data);

// Now you can use $json_data for any other purpose, such as sending it via AJAX

?>