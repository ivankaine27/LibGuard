<?php
include 'includes/session.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/Exception.php';
require 'C:/xampp/htdocs/libguard/admin/phpmailer/phpmailer/src/SMTP.php';

// Retrieve student information
if(isset($_POST['add'])){
    $student = $_POST['student'];
    
    $sql = "SELECT * FROM students WHERE student_id = '$student'";
    $query = $conn->query($sql);
    if($query->num_rows < 1){
        $_SESSION['error'][] = 'Student not found';
    }
    else{
        $row = $query->fetch_assoc();
        $student_id = $row['id'];
        $student_email = $row['email'];
        $student_name = $row['firstname'];

        // Construct email message with book details
        $bookDetails = '';
        $penaltyTotal = 0; // Initialize penalty total
		foreach ($_POST['isbn'] as $isbn) {
			$isbn = mysqli_real_escape_string($conn, $isbn);
			if (!empty($isbn)) {
				$sql = "SELECT b.*, br.due_date 
						FROM books b 
						INNER JOIN borrow br ON b.id = br.book_id 
						WHERE b.isbn = '$isbn' AND br.status = 0";
				$query = $conn->query($sql);
				if ($query->num_rows > 0) {
					$row = $query->fetch_assoc();
					$bookDetails .= "Title: " . $row['title'] . "<br>";
					$bookDetails .= "ISBN: " . $row['isbn'] . "<br>";
					// Fetch due date from the borrow table
					$dueDate = $row['due_date']; // Assuming due_date is the correct column name
					$bookDetails .= "Due Date: " . $dueDate . "<br><br>"; // Include due date in the email body
					$bookDetails .= "Borrowing Date: " . date('Y-m-d') . "<br><br>";
				
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

				}
			}
		}
		

        // Send email to the student
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';  // Specify SMTP server
            $mail->SMTPAuth   = true;                  // Enable SMTP authentication
            $mail->Username   = 'ivanbulaun2727@gmail.com';   // SMTP username
            $mail->Password   = 'dmye wbxl behj vkuw';        // SMTP password
            $mail->SMTPSecure = 'tls';                 // Enable TLS encryption
            $mail->Port       = 587;                   // TCP port to connect to

            //Recipients
            $mail->setFrom('ivanbulaun2727@gmail.com', 'LibGuard System');
            $mail->addAddress($student_email);        // Add a recipient

            // Content
            $mail->isHTML(true);  // Set email format to HTML
            $mail->Subject = 'Book Returned Successfully';
            $mail->Body    = 'Dear ' . $student_name . ',<br><br>' .
            'We are pleased to inform you that you have successfully returned the book you have borrowed using LibGuard, our advanced library management system.<br><br>' .
            'Book Details:<br>' .
            $bookDetails . '<br>' . 
            'Due Date: ' . $dueDate . '<br><br>' . 
            'Returned Date: ' . $returned_date . '<br><br>' . 
            'Penalties: PHP ' . number_format($penaltyTotal, 2) . '<br><br>' .  // Include the total penalty in the email body
            'Thank you for using LibGuard for your library needs.<br><br>' . 
            'Best regards,<br>' . 
            'LibGuard System Team';
            $mail->send();
            $_SESSION['success'] = 'Message has been sent';
        } catch (Exception $e) {
            $_SESSION['error'][] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Book return process
        $return = 0;
        foreach($_POST['isbn'] as $isbn){
            if(!empty($isbn)){
                $sql = "SELECT * FROM books WHERE isbn = '$isbn'";
                $query = $conn->query($sql);
                if($query->num_rows > 0){
                    $brow = $query->fetch_assoc();
                    $bid = $brow['id'];

                    $sql = "SELECT * FROM borrow WHERE student_id = '$student_id' AND book_id = '$bid' AND status = 0";
                    $query = $conn->query($sql);
                    if($query->num_rows > 0){
                        $borrow = $query->fetch_assoc();
                        $borrow_id = $borrow['id'];
                        $sql = "INSERT INTO returns (student_id, book_id, date_return) VALUES ('$student_id', '$bid', NOW())";
                        if($conn->query($sql)){
                            $return++;
                            $sql = "UPDATE books SET status = 0, quantity = quantity + 1 WHERE id = '$bid'";
                            $conn->query($sql);
                            $sql = "UPDATE borrow SET status = 1, penalty = '$penaltyTotal' WHERE id = '$borrow_id'";
                            $conn->query($sql);
                        }
                        else{
                            $_SESSION['error'][] = $conn->error;
                        }
                    }
                    else{
                        $_SESSION['error'][] = 'Borrow details not found: ISBN - '.$isbn.', Student ID: '.$student;
                    }
                }
                else{
                    $_SESSION['error'][] = 'Book not found: ISBN - '.$isbn;
                }
            }
        }

        if($return > 0){
            $book = ($return == 1) ? 'Book' : 'Books';
            $_SESSION['success'] = $return.' '.$book.' successfully returned';
        }
    }
}
else{
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: return.php');
?>
