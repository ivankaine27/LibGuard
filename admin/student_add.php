<?php
include 'includes/session.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/Exception.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/SMTP.php';


// Function to generate a random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $password;
}

if (isset($_POST['add'])) {
    $student_id = $_POST['student_id']; // Retrieve the manually inputted student ID
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $course = $_POST['course'];
    $filename = $_FILES['photo']['name'];

    // Generate a random password for the student
    $password = generateRandomPassword();

    $query = "SELECT id FROM course WHERE title = '$course'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $course_id = $row['id'];
    }
    if (!empty($filename)) {
        move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $filename);
    }

    // Insert the provided student ID and password into the database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO students (student_id, firstname, lastname, course_id, photo, password, created_on) VALUES ('$student_id', '$firstname', '$lastname', '$course_id', '$filename', '$hashed_password', NOW())";

    if ($conn->query($sql)) {
        $_SESSION['success'] = 'Student added successfully';

        // Send acknowledgment email to the student
        $student_email = $student_id . "@dhvsu.edu.ph";
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'ivanbulaun2727@gmail.com';
            $mail->Password   = 'dmye wbxl behj vkuw';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('ivanbulaun2727@gmail.com', 'LibGuard System');
            $mail->addAddress($student_email);
            $mail->isHTML(true);
            $mail->Subject = 'Welcome to LibGuard';
            $mail->Body    = 'Dear Student,<br><br>' .
                             'Welcome to LibGuard, our advanced library management system. You have successfully registered in the system.<br><br>' .
                             'Your temporary password is: ' . $password . '<br><br>' .
                             'Please use this password to login to the system. You will be prompted to change it after your first login.<br><br>' .
                             'Thank you for joining us!<br><br>' . 
                             'Best regards,<br>' . 
                             'LibGuard System Team';
            $mail->send();
            $_SESSION['success'] = 'Registration acknowledgment email has been sent';
        } catch (Exception $e) {
            $_SESSION['error'][] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = $conn->error;
    }
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: student.php');
?>
