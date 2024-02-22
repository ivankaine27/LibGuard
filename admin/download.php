<?php
    // Include necessary files and initialize the session if needed
    include 'includes/session.php';

    // Retrieve the selected student IDs from the URL parameter
    $selectedStudents = isset($_GET['students']) ? json_decode($_GET['students']) : array();

    // Check if there are selected students
    if (count($selectedStudents) > 0) {
        // Fetch student names from the database or any source
        // Assuming you have a database table named 'students'
        $studentNames = array();
        foreach ($selectedStudents as $studentId) {
            $sql = "SELECT firstname, lastname FROM students WHERE id = $studentId";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $studentNames[] = $row['firstname'] . ' ' . $row['lastname'];
            }
        }

        // Construct SQL query to fetch transaction history for selected students
        $studentIds = implode(', ', $selectedStudents);
        $sql = "SELECT * FROM borrow LEFT JOIN books ON books.id = borrow.book_id WHERE student_id IN ($studentIds) ORDER BY date_borrow DESC";

        // Execute the query to fetch transaction history
        $query = $conn->query($sql);
    } else {
        // If no students are selected, display a message or redirect as needed
        echo "No students selected.";
        exit; // Stop further execution
    }

    // Include your database connection and other necessary configurations here
?>

<?php
    // Fetch the student names
    $studentNamesString = implode(', ', $studentNames);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <!-- Include your CSS and other necessary stylesheets here -->
</head>
<body>
    <!-- Header section -->
    <header>
        <h1>Transaction History</h1>
        <p>Student Names: <?php echo $studentNamesString; ?></p>
    </header>

    <!-- Transaction history table -->
    <table>
        <thead>
            <tr>
                <th>Date Borrowed</th>
                <th>Date Returned</th>
                <th>ISBN</th>
                <th>Title</th>
                <th>Author</th>
            </tr>
        </thead>
        <tbody>
            <?php
                while ($row = $query->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['date_borrow'] . "</td>";
                    echo "<td>" . $row['date_return'] . "</td>";
                    echo "<td>" . $row['isbn'] . "</td>";
                    echo "<td>" . $row['title'] . "</td>";
                    echo "<td>" . $row['author'] . "</td>";
                    echo "</tr>";
                }
            ?>
        </tbody>
    </table>

    <!-- Button to trigger browser print function -->
    <button onclick="window.print()">Print</button>

    <!-- Include your JavaScript and other necessary scripts here -->
</body>
</html>
