<?php include 'includes/session.php'; ?>
<?php
// Include your database connection code here if needed

// Fetch data for the pie chart
$pieChartData = array();
$courseQuery = "SELECT course.title, COUNT(*) AS count
                FROM borrow
                LEFT JOIN students ON borrow.student_id = students.id
                LEFT JOIN course ON students.course_id = course.id";

if (isset($_GET['selected_courses'])) {
    $courseIds = explode(',', $_GET['selected_courses']);
    $courseIds = array_map('intval', $courseIds);
    $courseIdsString = implode(',', $courseIds);

    $courseQuery .= " WHERE course.id IN ($courseIdsString)";
}

if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
    $courseQuery .= " AND borrow.date_borrow BETWEEN '" . $_GET['startDate'] . "' AND '" . $_GET['endDate'] . "'";
}

$courseQuery .= " GROUP BY course.title";
$courseResult = $conn->query($courseQuery);

while ($courseRow = $courseResult->fetch_assoc()) {
    $pieChartData['labels'][] = $courseRow['title'];
    $pieChartData['data'][] = $courseRow['count'];
    // Add colors if needed
    $pieChartData['colors'][] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
}

echo json_encode($pieChartData);
?>
