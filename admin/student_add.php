<?php
    include 'includes/session.php';

    if (isset($_POST['add'])) {
        $student_id = $_POST['student_id']; // Retrieve the manually inputted student ID
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $course = $_POST['course'];
        $filename = $_FILES['photo']['name'];

        $query = "SELECT id FROM course WHERE title = '$course'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $course_id = $row['id'];
        }
        if (!empty($filename)) {
            move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $filename);
        }
    
        // Insert the provided student ID into the database
        $sql = "INSERT INTO students (student_id, firstname, lastname, course_id, photo, created_on) VALUES ('$student_id', '$firstname', '$lastname', '$course_id', '$filename', NOW())";
        
        if($conn->query($sql)){
            $_SESSION['success'] = 'Student added successfully';
        } else {
            $_SESSION['error'] = $conn->error;
        }

    } else {
        $_SESSION['error'] = 'Fill up add form first';
    }

    header('location: student.php');
?>
