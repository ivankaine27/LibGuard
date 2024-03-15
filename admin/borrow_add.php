<?php
include 'includes/session.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer classes
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/Exception.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/SMTP.php';

if (isset($_POST['add'])) {
    // Sanitize input
    $student = mysqli_real_escape_string($conn, $_POST['student']);

    // Retrieve student information
    $sql = "SELECT * FROM students WHERE student_id = '$student'";
    $query = $conn->query($sql);

    if ($query->num_rows < 1) {
        $_SESSION['error'][] = 'Student not found';
    } else {
        $row = $query->fetch_assoc();
        $student_id = $row['id'];
        $student_email = $row['email'];
        $student_name = $row['firstname'];

        // Construct email message with book details
        $bookDetails = '';
        foreach ($_POST['isbn'] as $isbn) {
            $isbn = mysqli_real_escape_string($conn, $isbn);
            if (!empty($isbn)) {
                $sql = "SELECT * FROM books WHERE isbn = '$isbn' AND quantity != 0";
                $query = $conn->query($sql);
                if ($query->num_rows > 0) {
                    $brow = $query->fetch_assoc();
                    $bookDetails .= "Title: " . $brow['title'] . "<br>";
                    $bookDetails .= "ISBN: " . $brow['isbn'] . "<br>";
                    $bookDetails .= "Borrowing Date: " . date('Y-m-d') . "<br>";
                } else {
                    // Provide more specific error message if book is not available
                    $_SESSION['error'][] = 'Book with ISBN - ' . $isbn . ' is already borrowed or not available';
                }
            }
        }

        // Calculate due date (1 week from borrowing date)
        $dueDate = date('Y-m-d', strtotime($date_borrow . ' + 7 days'));
        $formattedDueDate = date('Y-m-d', strtotime($dueDate));

        // Send email to the student
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
            $mail->Subject = 'Book Borrowed Successfully';
            $mail->Body    = 'Dear ' . $student_name . ',<br><br>' .
                             'We are pleased to inform you that you have successfully borrowed a book using LibGuard, our advanced library management system.<br><br>' .
                             'Book Details:<br>' .
                             $bookDetails . 
                             'Due Date: ' . $formattedDueDate . '<br><br>' . 
                             'Please ensure to return the book on or before the due date to avoid any late fees or penalties.<br><br>' . 
                             'Thank you for using LibGuard for your library needs.<br><br>' . 
                             'Best regards,<br>' . 
                             'LibGuard System Team';
            $mail->send();
            $_SESSION['success'] = 'Message has been sent';
        } catch (Exception $e) {
            // Provide detailed error message if email sending fails
            $_SESSION['error'][] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

// Process book borrowing
foreach ($_POST['isbn'] as $isbn) {
    $isbn = mysqli_real_escape_string($conn, $isbn);
    if (!empty($isbn)) {
        $sql = "SELECT * FROM books WHERE isbn = '$isbn' AND quantity != 0";
        $query = $conn->query($sql);
        if ($query->num_rows > 0) {
            $brow = $query->fetch_assoc();
            $bid = $brow['id'];

            // Retrieve the current quantity of the book
            $current_quantity = $brow['quantity'];

            // Update book status and quantity
            $sql_update_quantity = "UPDATE books SET quantity = quantity - 1 WHERE id = '$bid'";
            $result_update_quantity = $conn->query($sql_update_quantity);
            if ($result_update_quantity) {
                // Insert borrowing record
                $sql_insert_borrow = "INSERT INTO borrow (student_id, book_id, date_borrow, due_date) VALUES ('$student_id', '$bid', NOW(), '$formattedDueDate')";
                if ($conn->query($sql_insert_borrow)) {
                    $added++;
                    
                    // Check if the quantity is now 0 after the update
                    if ($current_quantity == 0) {
                        // Update the status of the book to 1 (borrowed)
                        $sql_update_status = "UPDATE books SET status = 1 WHERE id = '$bid'";
                        $conn->query($sql_update_status);
                    }
                } else {
                    $_SESSION['error'][] = $conn->error;
                }
            } else {
                $_SESSION['error'][] = 'Failed to update book quantity';
            }
        } else {
            // Provide more specific error message if book is not available
            $_SESSION['error'][] = 'Book with ISBN - ' . $isbn . ' is already borrowed or not available';
        }
    }
}


        if ($added > 0) {
            $book = ($added == 1) ? 'Book' : 'Books';
            $_SESSION['success'] = $added . ' ' . $book . ' successfully borrowed';
        }
    }
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: borrow.php');
exit;
?>
